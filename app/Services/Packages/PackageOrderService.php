<?php

namespace App\Services\Packages;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\SaleType;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Ecommerce\PackageOrderCheckout;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Settings\Product;
use App\Models\Settings\Warehouse;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Membership\CashoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PackageOrderService
{
    public function checkout(Supplier $customer, array $data): PackageOrderCheckout
    {
        if ($customer->status != 1 || $customer->type !== 'Customer') {
            ProductPackageService::fail('Active customer account required.');
        }

        if (($data['coupon_code'] ?? null)) {
            ProductPackageService::fail('Coupons are not available for this checkout yet. Remove the coupon to continue.');
        }
        if (! in_array($data['payment_method'], ['cod', 'membership'], true)) {
            ProductPackageService::fail('Select Cash on Delivery or Membership Account.');
        }
        $shipping = CashoutService::minor((string) ($data['shipping_charge'] ?? 0));
        if (! in_array($shipping, [5000, 10000], true)) {
            ProductPackageService::fail('Select the supported delivery charge (Tk 50 or Tk 100).');
        }
        $input = collect($data['items'])->map(fn ($i) => ['product_id' => (int) $i['product_id'], 'quantity' => (int) $i['quantity']])->sortBy('product_id')->values()->all();
        if (count(array_unique(array_column($input, 'product_id'))) !== count($input)) {
            ProductPackageService::fail('Duplicate cart products are not allowed.');
        }
        $fingerprint = [$input, $data['billing'], $shipping, $data['payment_method']];
        if (isset($data['expected_total'])) $fingerprint[] = CashoutService::money(CashoutService::minor($data['expected_total']));
        $hash = hash('sha256', json_encode($fingerprint));

        return DB::transaction(function () use ($customer, $data, $shipping, $input, $hash) {
            Supplier::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $old = PackageOrderCheckout::where('customer_id', $customer->id)->where('request_key', $data['request_key'])->first();
            if ($old) {
                if (! hash_equals($old->request_hash, $hash)) {
                    ProductPackageService::fail('This request key was used for another order.');
                }

                return $old;
            }
            $products = Product::whereIn('id', array_column($input, 'product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $warehouseId = null;
            $lines = [];
            $total = 0;
            foreach ($input as $item) {
                $product = $products->get($item['product_id']);
                if (! $product || $product->status != 1 || ! $product->is_show) {
                    ProductPackageService::fail('A product is no longer available.');
                }
                if ($product->isPackage()) {
                    if ($warehouseId && $warehouseId != $product->package_warehouse_id) {
                        ProductPackageService::fail('Packages from different warehouses must be ordered separately.');
                    }
                    $warehouseId = $product->package_warehouse_id;
                    $components = $product->packageItems()->with('product')->get();
                    if ($components->count() < 2) {
                        ProductPackageService::fail('Package components are unavailable.');
                    }
                    $parts = [];
                    foreach ($components as $component) {
                        $p = $component->product;
                        if (! $p || $p->status != 1 || $p->isPackage()) {
                            ProductPackageService::fail('A package component is unavailable.');
                        }$parts[] = ['product_id' => $p->id, 'name' => $p->name, 'quantity' => (int) $component->quantity, 'weight' => max(1, CashoutService::minor($p->selling_price))];
                    }
                } else {
                    $parts = [['product_id' => $product->id, 'name' => $product->name, 'quantity' => 1, 'weight' => 1]];
                }
                $price = CashoutService::minor($product->selling_price);
                if ($price < 1) {
                    ProductPackageService::fail('Product price is invalid.');
                }
                $lines[] = ['product_id' => $product->id, 'name' => $product->name, 'code' => $product->code, 'catalog_type' => $product->catalog_type, 'quantity' => $item['quantity'], 'unit_price' => CashoutService::money($price), 'line_total' => CashoutService::money($price * $item['quantity']), 'components' => $parts];
                $total += $price * $item['quantity'];
            }
            if (! $warehouseId) {
                // Ordinary products use one active warehouse that can fulfil the full order.
                // Prefer the customer's branch, then use a stable warehouse order.
                $candidates = Warehouse::where('status', 1)
                    ->whereHas('sisterConcern', fn ($q) => $q->where('status', 1))
                    ->orderByRaw('CASE WHEN sister_concern_id = ? THEN 0 ELSE 1 END', [(int) $customer->origin_sister_concern_id])
                    ->orderBy('id')->get();
                foreach ($candidates as $candidate) {
                    $canFulfil = true;
                    foreach ($this->demands($lines) as $productId => $quantity) {
                        $stock = Inventory::where('warehouse_id', $candidate->id)
                            ->where('sister_concern_id', $candidate->sister_concern_id)
                            ->where('product_id', $productId)->sum('quantity');
                        if ((float) $stock < $quantity) { $canFulfil = false; break; }
                    }
                    if ($canFulfil) { $warehouseId = $candidate->id; break; }
                }
                if (! $warehouseId) {
                    ProductPackageService::fail('Insufficient stock to fulfil this order from one warehouse.');
                }
            }
            $warehouse = Warehouse::whereKey($warehouseId)->lockForUpdate()->firstOrFail();
            $branch = SisterConcern::whereKey($warehouse->sister_concern_id)->lockForUpdate()->firstOrFail();
            if ($warehouse->status != 1 || $branch->status != 1) {
                ProductPackageService::fail('Fulfilment branch is inactive.');
            }
            $this->checkStock($lines, $warehouse);
            if ($data['payment_method'] === 'membership' && isset($data['expected_total'])
                && CashoutService::minor($data['expected_total']) !== $total + $shipping) {
                ProductPackageService::fail('The order total changed. Refresh your cart and review the total before paying.');
            }
            $package = $products->first(fn ($p) => $p->isPackage());
            $actor = User::where('status', 1)->find(($package ?? $products->first())->user_id);
            // Imported/older products may not have a surviving ERP creator.
            // This user is only the pending order's ERP reference; confirmation
            // records the actual operator and posts accounting separately.
            $actor ??= User::where('status', 1)->where('sister_concern_id', $branch->id)->orderBy('id')->first();
            $actor ??= User::where('status', 1)
                ->whereHas('roles', fn ($q) => $q->where('name', 'Admin'))
                ->orderBy('id')->first();
            if (! $actor) {
                ProductPackageService::fail('No active ERP user is available for this fulfilment branch. Please contact the branch.');
            }
            $order = SaleOrder::create(['sister_concern_id' => $branch->id, 'warehouse_id' => $warehouse->id, 'customer_id' => $customer->id, 'order_no' => ($package ? 'PKO-' : 'ESO-').Str::upper((string) Str::ulid()), 'type' => SaleType::$ECOMMERCE, 'sub_total' => CashoutService::money($total), 'discount' => 0, 'total' => CashoutService::money($total + $shipping), 'paid' => 0, 'due' => CashoutService::money($total + $shipping), 'notes' => 'Ecommerce order, pending stock confirmation', 'date' => now()->toDateString(), 'status' => 0, 'user_id' => $actor->id]);

            $checkout = PackageOrderCheckout::create(['sale_order_id' => $order->id, 'customer_id' => $customer->id, 'warehouse_id' => $warehouse->id, 'request_key' => $data['request_key'], 'request_hash' => $hash, 'billing' => $data['billing'], 'lines' => $lines, 'shipping_charge' => CashoutService::money($shipping), 'payment_method' => $data['payment_method']]);
            if ($data['payment_method'] === 'membership') {
                \App\Services\Membership\OrderPaymentService::capture($checkout, $actor->id);
            }
            return $checkout;
        }, 3);
    }

    private function demands(array $lines): array
    {
        $result = [];
        foreach ($lines as $line) {
            foreach ($line['components'] as $p) {
                $result[$p['product_id']] = ($result[$p['product_id']] ?? 0) + $p['quantity'] * $line['quantity'];
            }
        }ksort($result);

        return $result;
    }

    private function checkStock(array $lines, Warehouse $warehouse): void
    {
        foreach ($this->demands($lines) as $id => $qty) {
            $stock = Inventory::where('warehouse_id', $warehouse->id)->where('sister_concern_id', $warehouse->sister_concern_id)->where('product_id', $id)->sum('quantity');
            if ((float) $stock < $qty) {
                ProductPackageService::fail('Insufficient stock for product #'.$id.'.');
            }
        }
    }

    public function confirm(PackageOrderCheckout $checkout, User $operator): PackageOrderCheckout
    {
        abort_unless($operator->can('ecommerce_order_confirm'), 403);

        return DB::transaction(function () use ($checkout, $operator) {
            $checkout = PackageOrderCheckout::whereKey($checkout->id)->lockForUpdate()->firstOrFail();
            $order = SaleOrder::whereKey($checkout->sale_order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->sister_concern_id == $operator->activeConcernId(), 403);
            if ($checkout->status === 'Confirmed') {
                return $checkout;
            }
            if ($checkout->status !== 'Pending' || $order->status != 0) {
                ProductPackageService::fail('Only pending ecommerce orders can be confirmed.');
            }
            Supplier::whereKey($order->customer_id)->lockForUpdate()->firstOrFail();
            $warehouse = Warehouse::whereKey($checkout->warehouse_id)->lockForUpdate()->firstOrFail();
            $branch = SisterConcern::whereKey($order->sister_concern_id)->lockForUpdate()->firstOrFail();
            if ($warehouse->status != 1 || $warehouse->sister_concern_id != $branch->id || $branch->status != 1) {
                ProductPackageService::fail('Fulfilment warehouse is unavailable.');
            }
            $inventories = Inventory::where('warehouse_id', $warehouse->id)->where('sister_concern_id', $branch->id)->whereIn('product_id', array_keys($this->demands($checkout->lines)))->orderBy('id')->lockForUpdate()->get();
            foreach ($this->demands($checkout->lines) as $productId => $required) {
                if ((float) $inventories->where('product_id', $productId)->sum('quantity') < $required) {
                    ProductPackageService::fail('Insufficient component stock.');
                }
            }
            $cost = 0;
            foreach ($checkout->lines as $line) {
                $lineTotal = CashoutService::minor($line['line_total']);
                $weight = array_sum(array_map(fn ($p) => $p['weight'] * $p['quantity'], $line['components']));
                $allocated = 0;
                $parts = $line['components'];
                foreach ($parts as $index => $part) {
                    $quantity = $part['quantity'] * $line['quantity'];
                    $share = $index === array_key_last($parts) ? $lineTotal - $allocated : (int) floor($lineTotal * (($part['weight'] * $part['quantity']) / $weight));
                    $allocated += $share;
                    $base = intdiv($share, $quantity);
                    $extra = $share % $quantity;
                    foreach ([[$extra, $base + 1], [$quantity - $extra, $base]] as [$count,$selling]) {
                        if (! $count) {
                            continue;
                        }
                        foreach ($inventories->where('product_id', $part['product_id']) as $inventory) {
                            $take = min($count, (int) floor((float) $inventory->quantity));
                            if ($take < 1) {
                                continue;
                            }
                            SaleOrderItem::create(['sale_order_id' => $order->id, 'sister_concern_id' => $branch->id, 'product_id' => $part['product_id'], 'inventory_id' => $inventory->id, 'product_code' => $inventory->product_code, 'quantity' => $take, 'unit_price' => $inventory->unit_price, 'selling_price' => CashoutService::money($selling), 'date' => $order->date]);
                            $cost += (int) round((float) $inventory->unit_price * 100) * $take;
                            $inventory->quantity = (float) $inventory->quantity - $take;
                            $inventory->save();
                            InventoryLog::create(['sister_concern_id' => $branch->id, 'warehouse_id' => $warehouse->id, 'inventory_id' => $inventory->id, 'product_id' => $part['product_id'], 'product_code' => $inventory->product_code, 'batch_no' => $inventory->batch_no, 'sale_order_id' => $order->id, 'type' => 2, 'quantity' => $take, 'unit_price' => $inventory->unit_price, 'date' => $order->date, 'notes' => 'Ecommerce sale '.$order->order_no, 'user_id' => $operator->id]);
                            $count -= $take;
                            if (! $count) {
                                break;
                            }
                        }
                        if ($count) {
                            ProductPackageService::fail('Stock changed during confirmation. Retry.');
                        }
                    }
                }
            }
            $this->post($order, $cost, $operator);
            $order->status = 1;
            $order->user_id = $operator->id;
            $order->save();
            $checkout->status = 'Confirmed';
            $checkout->save();

            return $checkout;
        }, 3);
    }

    public function cancel(PackageOrderCheckout $checkout, User $operator): void
    {
        abort_unless($operator->can('ecommerce_order_confirm'), 403);
        DB::transaction(function () use ($checkout, $operator) {
            $checkout = PackageOrderCheckout::whereKey($checkout->id)->lockForUpdate()->firstOrFail();
            $order = SaleOrder::whereKey($checkout->sale_order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->sister_concern_id == $operator->activeConcernId(), 403);
            if ($checkout->status === 'Cancelled') return;
            abort_unless(in_array($checkout->status, ['Pending', 'Confirmed'], true) && $order->status < 2, 422, 'Only orders not yet dispatched can be cancelled.');
            abort_if($checkout->payment_method !== 'membership' && $order->paid > 0, 422, 'Refund collected COD payments through the collection workflow before cancelling.');
            if ($checkout->status === 'Confirmed') {
                foreach (SaleOrderItem::where('sale_order_id', $order->id)->orderBy('inventory_id')->get() as $item) {
                    $inventory = Inventory::withTrashed()->whereKey($item->inventory_id)->lockForUpdate()->firstOrFail();
                    if ($inventory->trashed()) $inventory->restore();
                    $inventory->increment('quantity', $item->quantity);
                    InventoryLog::create(['sister_concern_id' => $order->sister_concern_id, 'warehouse_id' => $checkout->warehouse_id,
                        'inventory_id' => $inventory->id, 'product_id' => $item->product_id, 'product_code' => $inventory->product_code,
                        'batch_no' => $inventory->batch_no, 'sale_order_id' => $order->id, 'type' => 1,
                        'quantity' => $item->quantity, 'unit_price' => $item->unit_price, 'date' => now()->toDateString(),
                        'notes' => 'Cancelled ecommerce order '.$order->order_no, 'user_id' => $operator->id]);
                }
                foreach (Voucher::where('sale_order_id', $order->id)->whereNull('payment_purpose')->get() as $voucher) {
                    \App\Services\Membership\OrderPaymentService::reverseVoucher($voucher, $operator->id, \App\Services\Membership\OrderPaymentService::CANCELLATION);
                }
            }
            \App\Services\Membership\OrderPaymentService::refund($checkout, $operator->id);
            $checkout->update(['status' => 'Cancelled']);
            $order->refresh()->update(['status' => 4, 'due' => 0]);
        }, 3);
    }

    private function post(SaleOrder $order, int $cost, User $operator): void
    {
        $receivable = AccountHead::where('supplier_id', $order->customer_id)->where('head_type', 'primary')->where('status', 1)->first();
        if (! $receivable) {
            $group = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->firstOrFail();
            $receivable = AccountHead::create(['name' => $order->customer->name.' (Customer)', 'nature' => 'Debit', 'account_group_id' => $group->id, 'supplier_id' => $order->customer_id, 'head_type' => 'primary', 'status' => 1]);
        }
        if ($receivable->nature !== 'Debit') {
            ProductPackageService::fail('Customer receivable ledger has an invalid nature.');
        }
        $number = generateVoucherNo(1, 'vouchers', $order->sister_concern_id);
        $voucher = Voucher::create(['sale_order_id' => $order->id, 'sister_concern_id' => $order->sister_concern_id, 'voucher_type' => 1, 'voucher_no' => $number, 'voucher_no_group_sl' => (int) filter_var($number, FILTER_SANITIZE_NUMBER_INT), 'date' => $order->date, 'amount' => CashoutService::money(CashoutService::minor($order->total) + $cost), 'notes' => 'Ecommerce sale '.$order->order_no, 'user_id' => $operator->id]);
        $entries = [[$receivable->id, 1, CashoutService::minor($order->total)], [reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), 2, CashoutService::minor($order->total)]];
        if ($order->paid > 0 && PackageOrderCheckout::where('sale_order_id', $order->id)->where('payment_method', 'membership')->exists()) {
            $advance = \App\Services\Membership\OrderPaymentService::head(ReservedAccountHead::CUSTOMER_ADVANCE_RECEIVED, 'Credit');
            $entries[] = [$advance, 1, CashoutService::minor($order->paid)];
            $entries[] = [$receivable->id, 2, CashoutService::minor($order->paid)];
            $voucher->update(['amount' => CashoutService::money(CashoutService::minor($order->total) + $cost + CashoutService::minor($order->paid))]);
        }
        if ($cost) {
            $entries[] = [reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), 1, $cost];
            $entries[] = [reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), 2, $cost];
        }
        foreach ($entries as [$head,$type,$amount]) {
            if (! $head) {
                ProductPackageService::fail('Required sales accounting head is missing.');
            }Transaction::create(['sister_concern_id' => $order->sister_concern_id, 'voucher_id' => $voucher->id, 'sale_order_id' => $order->id, 'account_head_id' => $head, 'voucher_type' => 1, 'transaction_type' => $type, 'amount' => CashoutService::money($amount), 'date' => $order->date, 'voucher_no' => $number, 'voucher_no_group_sl' => $voucher->voucher_no_group_sl, 'notes' => $voucher->notes, 'user_id' => $operator->id]);
        }
    }

    public function data(PackageOrderCheckout $checkout): array
    {
        $order = $checkout->saleOrder;

        return ['is_package_order' => collect($checkout->lines)->contains(fn ($line) => $line['catalog_type'] === 'package'), 'is_snapshot_order' => true, 'id' => $order->id, 'order_no' => $order->order_no, 'date' => $order->date, 'status' => $order->status, 'package_status' => $checkout->status, 'billing_name' => $checkout->billing['name'], 'billing_mobile_no' => $checkout->billing['phone'], 'billing_address' => $checkout->billing['address'], 'payment_method' => $checkout->payment_method, 'shipping_charge' => $checkout->shipping_charge, 'sub_total' => $order->sub_total, 'discount' => 0, 'total' => $order->total, 'paid' => $order->paid, 'due' => $order->due, 'products' => array_map(fn ($line) => $line + ['discount_amt' => 0], $checkout->lines)];
    }
}
