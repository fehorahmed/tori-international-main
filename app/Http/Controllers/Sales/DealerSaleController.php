<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\InventoryLogType;
use App\Enumeration\SaleType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\SupplierType;
use App\Enumeration\PaymentPurpose;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\IncentiveManagement\IncentiveLedger;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Sales\SalePayment;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use App\Models\Hrm\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Yajra\DataTables\Facades\DataTables;

class DealerSaleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('dealer_sales')) {
            abort(403, 'Unauthorized');
        }

        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $customers = Supplier::where('status', 1)
            ->where('type', SupplierType::$DEALER)
            ->get();

        return view('sales.dealer_sales.index', compact('paymentModes', 'customers'));
    }

    public function dataTable()
    {
        $query = SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SaleType::$DEALER)
            ->with('customer', 'saleOrderItems.product');

        if (request()->has('start_date') && request('start_date') != '') {
            $query->where('date', '>=', Carbon::parse(request('start_date'))->format('Y-m-d'));
        }
        if (request()->has('end_date') && request('end_date') != '') {
            $query->where('date', '<=', Carbon::parse(request('end_date'))->format('Y-m-d'));
        }
        if (request()->has('customer') && request('customer') != '') {
            $query->where('customer_id', request('customer'));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (SaleOrder $sales_order) {
                $btn = '';
                // if (auth()->user()->hasPermissionTo('sales_payment_receive')) {
                //     if ($sales_order->due > 0)
                //         $btn .= '<a role="button" data-id="' . $sales_order->id . '" data-total="' . $sales_order->total . '" data-due="' . $sales_order->due . '" data-order_no="' . $sales_order->order_no . '" class="btn btn-primary bg-gradient-primary btn-sm customer-pay">Paid</a>';
                // }

                $btn .= ' <a href="' . route('dealer-sales.show', ['dealer_sale' => $sales_order->id]) . '" class="btn btn-info bg-gradient-info btn-sm" title="Details"><i class="fa fa-info-circle"></i></a>';
                // if (auth()->user()->hasPermissionTo('dealer_sales_edit')) {
                //     $btn .= ' <a href="' . route('dealer-sales.edit', ['dealer_sale' => $sales_order->id]) . '" class="btn btn-warning bg-gradient-warning btn-sm"><i class="fa fa-edit"></i></a>';
                // }
                // $btn .= ' <a href="' . route('dealer-sales.print', ['dealer_sale' => $sales_order->id]) . '" target="_blank" class="btn btn-default btn-sm" title="Print"><i class="fa fa-print"></i></a>';
                if ($sales_order->hasActiveWarrantyItems()) {
                    $btn .= ' <a href="' . route('service.request.create', ['sale_type' => SaleType::$DEALER, 'sale_order_id' => $sales_order->id]) . '" class="btn btn-warning bg-gradient-warning btn-sm" title="Service Request"><i class="fa fa-cogs text-white"></i></a>';
                }

                if (auth()->user()->can('dealer_sales_delete')) {
                    if ($sales_order->salesReturns()->exists()) {
                        $btn .= ' <a role="button" class="btn btn-secondary btn-sm" title="Cannot delete: Return orders exist"><i class="fa fa-lock"></i></a>';
                    } else {
                        $btn .= ' <a role="button" data-id="' . $sales_order->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }

                return dropdownMenuContainer($btn);
            })
            ->addColumn('customer_name', function (SaleOrder $sales_order) {
                return $sales_order->customer->name ?? '';
            })
            ->addColumn('order_no', function (SaleOrder $sales_order) {
                return '<a href="' . route('dealer-sales.show', ['dealer_sale' => $sales_order->id]) . '">' . $sales_order->order_no . '</a>';
            })
            ->rawColumns(['action', 'order_no'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('dealer_sales_create')) {
            abort(403, 'Unauthorized');
        }
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->withSum([
            'inventories' => function ($q) {
                $q->where('sister_concern_id', auth()->user()->activeConcernId());
            }
        ], 'quantity')->get();
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $customers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->where('type', SupplierType::$DEALER)
            ->get();
        $srs = Employee::where('status', 1)->where('sister_concern_id', auth()->user()->activeConcernId())->srs()->get();

        return view('sales.dealer_sales.create', compact('warehouses', 'products', 'paymentModes', 'customers', 'srs'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('dealer_sales_create')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'sr' => 'required',
            'memo_no' => 'required',
            'notes' => 'nullable|max:255',
            'warehouse' => 'required',
            'customer' => 'required',
            'date' => 'required|date',
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'discount_type' => 'required|in:1,2',
            'discount_percent' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric|max:' . ($request->total_amount),
            'due_amount' => 'required|numeric|min:0',
        ];

        if (($request->paid_amount ?? 0) > 0) {
            $rules['payment_method'] = 'required';
        }
        $request->validate($rules);

        $customer = Supplier::find($request->customer);
        if ($customer && $customer->customer_current_balance > 0) {
            return redirect()->back()->with('error', 'This client has previous dues. You cannot generate a new order until it is paid.')->withInput();
        }

        if ($customer && $customer->hasPendingPreOrder()) {
            return redirect()->back()->with('error', 'This client has an unresolved Pre-Order. Please deliver or cancel it first.')->withInput();
        }

        if ($request->discount_type == 1) {
            // Flat BDT
            $request['discount_amount'] = $request->discount_percent ?? 0;
            $request['discount_percent'] = 0;
        } else {
            // Percentage (%)
            $request['discount_amount'] = 0;
            if (($request->discount_percent ?? 0) > 0) {
                $request['discount_amount'] = ($request->sub_total / 100) * $request->discount_percent;
            }
        }

        DB::beginTransaction();
        try {
            $counter = 0;
            if (!$request->inventory_id) {
                throw new \Exception("Please add at least one product.");
            }
            foreach ($request->inventory_id as $reqInventoryId) {
                $product = Product::find($request->product_id[$counter]);
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                    throw new \Exception("$product->name insufficient stock!");
                }
                $counter++;
            }

            $sisterConcern = auth()->user()->sisterConcern;
            $areaManagerId = $sisterConcern->area_manager_id ?? null;
            $branchManagerId = $sisterConcern->branch_manager_id ?? null;

            $saleOrder = new SaleOrder();
            $saleOrder->sister_concern_id = auth()->user()->activeConcernId();
            $saleOrder->area_manager_id = $areaManagerId;
            $saleOrder->branch_manager_id = $branchManagerId;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->warehouse_id = $request->warehouse;
            $saleOrder->order_no = generateOrderNo('sale_orders', 'order_no', 'DSO-');
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->vat_percent = 0;
            $saleOrder->vat_amount = 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->advance_adjust = $request->advance_amount ?? 0;
            $saleOrder->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->notes = $request->notes;
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->type = SaleType::$DEALER;
            $saleOrder->user_id = auth()->id();
            $saleOrder->save();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->inventory_id as $reqInventoryId) {
                $product = Product::find($request->product_id[$counter]);
                $inventory = Inventory::find($request->inventory_id[$counter]);

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = \Carbon\Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $product->id;
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);

                $inventoryLog = new InventoryLog();
                $inventoryLog->type = InventoryLogType::$SALE_OUT;
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $request->customer;
                $inventoryLog->sale_order_id = $saleOrder->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_quantity[$counter];
                $inventoryLog->unit_price = $request->product_unit_price[$counter];
                $inventoryLog->product_code = $request->product_code[$counter];
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();
                $counter++;
            }

            $this->createAccountingEntries($saleOrder, $costingTotal, $request);

            DB::commit();
            return redirect()->route('dealer-sales.show', ['dealer_sale' => $saleOrder->id])->with('success', 'Dealer Sale created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function createAccountingEntries($saleOrder, $costingTotal, $request)
    {
        $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
        $payeeId = $customerAccountHead->id;
        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
        $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = new Voucher();
        $voucher->sister_concern_id = auth()->user()->activeConcernId();
        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $voucher->amount = $saleOrder->total + $saleOrder->discount + $costingTotal;
        $voucher->date = $saleOrder->date->format('Y-m-d');
        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
        $voucher->voucher_no = $voucherNo;
        $voucher->account_head_payee_depositor_id = $payeeId;
        $voucher->sale_order_id = $saleOrder->id;
        $voucher->user_id = auth()->id();
        $voucher->save();

        $this->transaction($voucher, $payeeId, TransactionType::$DEBIT, $saleOrder->total, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id);

        if ($saleOrder->discount > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_DISCOUNT), TransactionType::$DEBIT, $saleOrder->discount, $saleOrder->id);
        }

        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), TransactionType::$DEBIT, $costingTotal, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), TransactionType::$CREDIT, $costingTotal, $saleOrder->id);

        $advanceAdjustAmount = $request->advance_amount ?? 0;
        if ($advanceAdjustAmount > 0) {
            // Accounting handled by voucher if needed, SalePayment removed.
        }

        $cashPaidAmount = $request->paid_amount ?? 0;
        if ($cashPaidAmount > 0) {
            $paymentVoucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
            $paymentVoucherNoGroupSl = abs((int) filter_var($paymentVoucherNo, FILTER_SANITIZE_NUMBER_INT));

            $paymentVoucher = new Voucher();
            $paymentVoucher->sister_concern_id = auth()->user()->activeConcernId();
            $paymentVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $paymentVoucher->payment_type_id = AccountHead::find($request->payment_method)->payment_mode ?? null;
            $paymentVoucher->payment_account_head_id = $request->payment_method;
            $paymentVoucher->amount = $cashPaidAmount;
            $paymentVoucher->date = $saleOrder->date->format('Y-m-d');
            $paymentVoucher->voucher_no_group_sl = $paymentVoucherNoGroupSl;
            $paymentVoucher->voucher_no = $paymentVoucherNo;
            $paymentVoucher->account_head_payee_depositor_id = $payeeId;
            $paymentVoucher->sale_order_id = $saleOrder->id;
            $paymentVoucher->payment_purpose = PaymentPurpose::$INSTANT_COLLECTION;
            $paymentVoucher->user_id = auth()->id();
            $paymentVoucher->save();

            $this->transaction($paymentVoucher, $request->payment_method, TransactionType::$DEBIT, $cashPaidAmount, $saleOrder->id, PaymentPurpose::$INSTANT_COLLECTION);
            $this->transaction($paymentVoucher, $payeeId, TransactionType::$CREDIT, $cashPaidAmount, $saleOrder->id, PaymentPurpose::$INSTANT_COLLECTION);
        }
    }

    private function transaction($voucher, $headId, $type, $amount, $saleOrderId, $paymentPurpose = null)
    {
        $transaction = new Transaction();
        $transaction->sister_concern_id = auth()->user()->activeConcernId();
        $transaction->voucher_id = $voucher->id;
        $transaction->account_head_id = $headId;
        $transaction->voucher_type = $voucher->voucher_type;
        $transaction->transaction_type = $type;
        $transaction->amount = $amount;
        $transaction->date = $voucher->date;
        $transaction->voucher_no_group_sl = $voucher->voucher_no_group_sl;
        $transaction->voucher_no = $voucher->voucher_no;
        $transaction->sale_order_id = $saleOrderId;
        $transaction->payment_purpose = $paymentPurpose;
        $transaction->user_id = auth()->id();
        $transaction->save();
    }

    public function edit(SaleOrder $dealerSale)
    {
        if ($dealerSale->type != SaleType::$DEALER)
            abort(404);
        if (!auth()->user()->hasPermissionTo('dealer_sales_edit')) {
            abort(403, 'Unauthorized');
        }
        $dealerSale->load('saleOrderItems.product', 'saleOrderItems.inventory', 'vouchers.transactions');
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->withSum([
            'inventories' => function ($q) {
                $q->where('sister_concern_id', auth()->user()->activeConcernId());
            }
        ], 'quantity')->get();
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $customers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->where('type', SupplierType::$DEALER)
            ->get();
        $srs = Employee::where('status', 1)->where('sister_concern_id', auth()->user()->activeConcernId())->srs()->get();
        $selectedPaymentMethod = '';

        return view('sales.dealer_sales.edit', compact('warehouses', 'products', 'paymentModes', 'customers', 'dealerSale', 'srs', 'selectedPaymentMethod'));
    }

    public function update(Request $request, SaleOrder $dealerSale)
    {
        if ($dealerSale->type != SaleType::$DEALER)
            abort(404);
        if (!auth()->user()->hasPermissionTo('dealer_sales_edit')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'sr' => 'required',
            'memo_no' => 'required',
            'notes' => 'nullable|max:255',
            'warehouse' => 'required',
            'customer' => 'required',
            'date' => 'required|date',
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'discount_type' => 'required|in:1,2',
            'discount_percent' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric|max:' . ($request->total_amount),
            'due_amount' => 'required|numeric|min:0',
        ];

        if (($request->paid_amount ?? 0) > 0) {
            $rules['payment_method'] = 'required';
        }
        $request->validate($rules);

        if ($request->discount_type == 1) {
            // Flat BDT
            $request['discount_amount'] = $request->discount_percent ?? 0;
            $request['discount_percent'] = 0;
        } else {
            // Percentage (%)
            $request['discount_amount'] = 0;
            if (($request->discount_percent ?? 0) > 0) {
                $request['discount_amount'] = ($request->sub_total / 100) * $request->discount_percent;
            }
        }

        DB::beginTransaction();
        try {
            // Revert Old Stock
            foreach ($dealerSale->saleOrderItems as $oldItem) {
                $oldInventory = Inventory::find($oldItem->inventory_id);
                if ($oldInventory) {
                    $oldInventory->increment('quantity', $oldItem->quantity);
                }
            }

            // Delete Old Data
            SaleOrderItem::where('sale_order_id', $dealerSale->id)->delete();
            InventoryLog::where('sale_order_id', $dealerSale->id)->delete();

            // Delete Old Accounting Entries
            $vouchers = Voucher::where('sale_order_id', $dealerSale->id)->get();
            foreach ($vouchers as $v) {
                Transaction::where('voucher_id', $v->id)->delete();
                $v->delete();
            }

            // Stock Check for New Data
            $counter = 0;
            if (!$request->inventory_id) {
                throw new \Exception("Please add at least one product.");
            }
            foreach ($request->inventory_id as $reqInventoryId) {
                $product = Product::find($request->product_id[$counter]);
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                    throw new \Exception("$product->name insufficient stock!");
                }
                $counter++;
            }

            // Update Sale Order
            $dealerSale->warehouse_id = $request->warehouse;
            $dealerSale->sr_id = $request->sr;
            $dealerSale->date = Carbon::parse($request->date);
            $dealerSale->customer_id = $request->customer;
            $dealerSale->sub_total = $request->sub_total;
            $dealerSale->discount_type = $request->discount_type;
            $dealerSale->discount_percent = $request->discount_percent ?? 0;
            $dealerSale->discount = $request->discount_amount ?? 0;
            $dealerSale->total = $request->total_amount;
            $dealerSale->advance_adjust = $request->advance_amount ?? 0;
            $dealerSale->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $dealerSale->due = $request->due_amount ?? 0;
            $dealerSale->notes = $request->notes;
            $dealerSale->memo_no = $request->memo_no;
            $dealerSale->save();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->inventory_id as $reqInventoryId) {
                $product = Product::find($request->product_id[$counter]);
                $inventory = Inventory::find($request->inventory_id[$counter]);

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $dealerSale->id;
                $saleItem->date = \Carbon\Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $product->id;
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);

                $inventoryLog = new InventoryLog();
                $inventoryLog->type = InventoryLogType::$SALE_OUT;
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $request->customer;
                $inventoryLog->sale_order_id = $dealerSale->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->quantity = $request->product_quantity[$counter];
                $inventoryLog->unit_price = $request->product_unit_price[$counter];
                $inventoryLog->product_code = $request->product_code[$counter];
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();
                $counter++;
            }

            $this->createAccountingEntries($dealerSale, $costingTotal, $request);

            DB::commit();
            return redirect()->route('dealer-sales.show', ['dealer_sale' => $dealerSale->id])->with('success', 'Dealer Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SaleOrder $dealerSale)
    {
        if ($dealerSale->type != SaleType::$DEALER)
            abort(404);
        $dealerSale->load('saleOrderItems.product.unit', 'customer');
        return view('sales.dealer_sales.details', ['sale' => $dealerSale]);
    }

    public function printInvoice(SaleOrder $dealerSale)
    {
        if ($dealerSale->type != SaleType::$DEALER)
            abort(404);
        $dealerSale->load('saleOrderItems.product.unit', 'saleOrderItems.inventory', 'customer', 'user');

        // Group items by product_id AND selling_price for Customer Copy
        $groupedItems = $dealerSale->saleOrderItems->groupBy(function ($item) {
            return $item->product_id . '-' . $item->selling_price;
        })->map(function ($items) {
            $firstItem = $items->first();
            return (object) [
                'product' => $firstItem->product,
                'product_code' => $firstItem->product_code,
                'total_quantity' => $items->sum('quantity'),
                'selling_price' => $firstItem->selling_price,
                'total_price' => $items->sum(function ($item) {
                    return $item->quantity * $item->selling_price;
                }),
                'batches' => $items->map(function ($item) {
                    return 'Batch-' . $item->inventory->batch_no;
                })->unique()->implode(', '),
            ];
        });

        return view('sales.dealer_sales.print', [
            'sale' => $dealerSale,
            'groupedItems' => $groupedItems
        ]);
    }
    public function destroy(SaleOrder $dealer_sale)
    {
        if (!auth()->user()->can('dealer_sales_delete')) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Check for Sales Returns
            // if (\App\Models\SalesReturn::where('sale_order_id', $dealer_sale->id)->exists()) {
            //     return response()->json(['success' => false, 'message' => 'Cannot delete: Invoice has associated Sales Returns.'], 200);
            // }

            // Check if there are any vouchers created later (e.g. from CustomerPaymentController)
            $hasLaterPayments = Voucher::where('sale_order_id', $dealer_sale->id)
                ->whereHas('transactions', function ($q) {
                    $q->whereNull('sale_order_id');
                })->exists();

            if ($hasLaterPayments) {
                return response()->json(['success' => false, 'message' => 'Cannot delete: Invoice has subsequent Customer Payments. Please delete the payments first.'], 200);
            }

            $saleItems = SaleOrderItem::where('sale_order_id', $dealer_sale->id)->get();
            foreach ($saleItems as $saleItem) {
                $inventory = Inventory::where('id', $saleItem->inventory_id)->first();
                if ($inventory) {
                    $inventory->increment('quantity', $saleItem->quantity);
                }
            }
            SaleOrderItem::where('sale_order_id', $dealer_sale->id)->delete();
            InventoryLog::where('sale_order_id', $dealer_sale->id)->delete();
            Voucher::where('sale_order_id', $dealer_sale->id)->delete();
            Transaction::where('sale_order_id', $dealer_sale->id)->delete();

            // Delete incentive entries so they don't get paid for a deleted sale
            $ledgers = IncentiveLedger::where('reference_type', SaleOrder::class)
                ->where('reference_id', $dealer_sale->id)
                ->get();
            foreach ($ledgers as $ledger) {
                if ($ledger->payment_status === 'paid') {
                    throw new \Exception("এই সেলসটি ডিলিট করা সম্ভব নয়! কারণ এই সেলসের বিপরীতে জেনারেট হওয়া ইনসেনটিভের টাকা ইতিমধ্যে এমপ্লয়িকে পেমেন্ট করা হয়ে গেছে।");
                }
                $ledger->delete();
            }

            // Delete the sale order
            $dealer_sale->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sales order deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Sales order not found: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }
}

