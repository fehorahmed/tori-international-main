<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\RoleType;
use App\Enumeration\SaleType;
use App\Enumeration\SalePaymentType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\SupplierType;
use App\Enumeration\PaymentPurpose;
use App\Models\Hrm\Employee;
use App\Rules\BusinessDate;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PreOrderController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('pre_orders')) {
            abort(403, 'Unauthorized');
        }
        $paymentModes = AccountHead::where('payment_mode', '>', 0)
            ->where(function($q) {
                $q->whereNull('sister_concern_id')
                  ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            })
            ->get();
        $customers = Supplier::where('status', 1)
            ->where('type', SupplierType::$CUSTOMER)
            ->get();
        $srs = Employee::where('status', 1)->where('sister_concern_id',auth()->user()->activeConcernId())
            ->srs()
            ->get();
        return view('sales.pre_order.index', compact('paymentModes', 'customers', 'srs'));
    }

    public function dataTable()
    {
        $query = SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SaleType::$PRE_ORDER)
            ->with('customer', 'sr');

        return DataTables::eloquent($query)
            ->addColumn('action', function (SaleOrder $sales_order) {
                $btn = '';
                if ($sales_order->is_delivered == 0) {
                    $btn .= ' <a href="' . route('pre-orders.delivery', ['pre_order' => $sales_order->id]) . '" class="btn btn-success bg-gradient-success btn-sm">Delivery</a>';
                    // $btn .= ' <a href="' . route('pre-orders.edit', ['pre_order' => $sales_order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-edit"></i></a>';
                } else {
                    $btn .= '<span class="badge bg-success">Delivered</span> ';
                }
                $btn .= ' <a href="' . route('pre-orders.show', ['pre_order' => $sales_order->id]) . '" class="btn btn-info bg-gradient-info btn-sm" title="Details"><i class="fa fa-info-circle"></i></a>';
                // $btn .= ' <a href="' . route('pre-orders.print', ['pre_order' => $sales_order->id]) . '" target="_blank" class="btn btn-default btn-sm" title="Print"><i class="fa fa-print"></i></a>';
                if (auth()->user()->can('pre_orders_delete')) {
                    $btn .= ' <a role="button" data-id="' . $sales_order->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('customer_name', function (SaleOrder $sales_order) {
                return $sales_order->customer->name ?? '';
            })
            ->addColumn('sr_name', function (SaleOrder $sales_order) {
                return $sales_order->sr->name ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function show(SaleOrder $pre_order)
    {
        $sale = $pre_order;
        $sale->load('saleOrderItems.product.unit', 'customer', 'user', 'sr', 'warehouse');
        return view('sales.pre_order.details', compact('sale'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('pre_orders_create')) {
            abort(403, 'Unauthorized');
        }
        $products = Product::where('status', 1)
            ->with(['unit', 'brand', 'productCategory', 'inventories' => function($query) {
                $query->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->orderBy('id', 'desc');
            }])
            ->get();

        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->srs()->get();

        $selectedCustomer = null;
        if (old('customer')) {
            $selectedCustomer = Supplier::find(old('customer'));
        }

        return view('sales.pre_order.create', compact('products', 'paymentModes', 'srs', 'selectedCustomer'));
    }

    public function edit(SaleOrder $pre_order)
    {
        if ($pre_order->type != SaleType::$PRE_ORDER) abort(404);
        if (!auth()->user()->hasPermissionTo('pre_orders_edit')) {
            abort(403, 'Unauthorized');
        }

        $pre_order->load('saleOrderItems.product');

        $products = Product::where('status', 1)
            ->with(['unit', 'brand', 'productCategory', 'inventories' => function($query) {
                $query->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->orderBy('id', 'desc');
            }])
            ->get();



        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->srs()->get();
        $customers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->where('type', SupplierType::$CUSTOMER)->get();

        $oldPaymentTransaction = Transaction::where('sale_order_id', $pre_order->id)
            ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
            ->where('transaction_type', TransactionType::$DEBIT)
            ->first();
        $selectedPaymentMethod = $oldPaymentTransaction->account_head_id ?? null;

        $sale = $pre_order;
        return view('sales.pre_order.edit', compact('products', 'paymentModes', 'srs', 'customers', 'pre_order', 'sale', 'selectedPaymentMethod'));
    }

    public function store(Request $request)
    {
        $rules = [
            'customer' => 'required',
            'date' => 'required|date',
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required|exists:suppliers,id',
            'discount_type' => 'required|in:1,2',
        ];
        if (($request->paid_amount ?? 0) > 0 && $request->advance_adjust != 1) {
            $rules['payment_method'] = 'required';
            $rules['rm_no'] = 'required';
        }
        $request->validate($rules);

        $customer = Supplier::find($request->customer);
        $currentBalance = $customer ? $customer->customer_current_balance : 0;

        if ($currentBalance > 0) {
            return redirect()->back()->with('error', 'This client has previous dues. You cannot generate a new order until it is paid.')->withInput();
        }

        if ($customer && $customer->hasPendingPreOrder()) {
            return redirect()->back()->with('error', 'This client has an unresolved Pre-Order. Please deliver or cancel it first.')->withInput();
        }

        DB::beginTransaction();
        try {
            $sisterConcern = auth()->user()->sisterConcern;
            $areaManagerId = $sisterConcern->area_manager_id ?? null;
            $branchManagerId = $sisterConcern->branch_manager_id ?? null;

            $saleOrder = new SaleOrder();
            $saleOrder->sister_concern_id = auth()->user()->activeConcernId();
            $saleOrder->area_manager_id = $areaManagerId;
            $saleOrder->branch_manager_id = $branchManagerId;
            $saleOrder->order_no = generateOrderNo('sale_orders', 'order_no', 'ASO-');
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->vat_percent = $request->vat_percent ?? 0;
            $saleOrder->vat_amount = $request->vat_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->advance_adjust = 0;
            $saleOrder->paid = $request->paid_amount;
            $saleOrder->due = 0;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->type = SaleType::$PRE_ORDER;
            $saleOrder->is_delivered = 0;
            $saleOrder->status = 1; 
            $saleOrder->user_id = auth()->id();
            $saleOrder->save();

            $counter = 0;
            foreach ($request->product_id as $productId) {
                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->product_id = $productId;
                $saleItem->product_code = $request->product_code[$counter] ?? null;
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = 0; 
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();
                $counter++;
            }

            $this->createAccountingEntries($saleOrder, 0, $request, $request->payment_method, true);

            DB::commit();
            return redirect()->route('pre-orders.index')->with('success', 'Pre-Order recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(SaleOrder $pre_order, Request $request)
    {
        if ($pre_order->is_delivered == 1) abort(403);
        
        $rules = [
            'customer' => 'required',
            'date' => 'required|date',
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required|exists:suppliers,id',
            'discount_type' => 'required|in:1,2',
        ];
        if (($request->paid_amount ?? 0) > 0 && $request->advance_adjust != 1) {
            $rules['payment_method'] = 'required';
            $rules['rm_no'] = 'required';
        }
        $request->validate($rules);

        DB::beginTransaction();
        try {
            $sisterConcern = auth()->user()->sisterConcern;
            $areaManagerId = $sisterConcern->area_manager_id ?? null;
            $branchManagerId = $sisterConcern->branch_manager_id ?? null;

            $saleOrder = $pre_order;
            $saleOrder->area_manager_id = $areaManagerId;
            $saleOrder->branch_manager_id = $branchManagerId;
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->advance_adjust = 0;
            $saleOrder->paid = 0;
            $saleOrder->due = 0;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->vat_percent = $request->vat_percent ?? 0;
            $saleOrder->vat_amount = $request->vat_amount ?? 0;
            $saleOrder->status = 1; 
            $saleOrder->save();

            SaleOrderItem::where('sale_order_id', $saleOrder->id)->delete();
            $counter = 0;
            foreach ($request->product_id as $productId) {
                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->product_id = $productId;
                $saleItem->product_code = $request->product_code[$counter] ?? null;
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = 0;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();
                $counter++;
            }

            $paymentMethod = $request->payment_method;
            if (!$paymentMethod) {
                $oldPaymentTransaction = Transaction::where('sale_order_id', $saleOrder->id)
                    ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->first();
                $paymentMethod = $oldPaymentTransaction->account_head_id ?? null;
            }

            Voucher::where('sale_order_id', $saleOrder->id)->delete();
            Transaction::where('sale_order_id', $saleOrder->id)->delete();

            $this->createAccountingEntries($saleOrder, 0, $request, $paymentMethod, true);

            DB::commit();
            return redirect()->route('pre-orders.index')->with('success', 'Pre-Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function delivery(SaleOrder $pre_order)
    {
        if ($pre_order->type != SaleType::$PRE_ORDER) abort(404);
        if ($pre_order->is_delivered == 1) abort(403, 'Already delivered');

        $pre_order->load('saleOrderItems.product');
        $products = Product::where('status', 1)->get();
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();

        $availableAdvance = 0;
        if ($pre_order->customer && $pre_order->customer->customer_current_balance < 0) {
            $availableAdvance = abs($pre_order->customer->customer_current_balance);
        }

        $sale = $pre_order;
        return view('sales.pre_order.delivery', compact('pre_order', 'warehouses', 'paymentModes', 'sale', 'products', 'availableAdvance'));
    }

    public function deliveryStore(SaleOrder $pre_order, Request $request)
    {
        if ($pre_order->is_delivered == 1) abort(403);

        $request->validate([
            'warehouse' => 'required',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'inventory_id' => 'required|array',
            'product_quantity' => 'required|array',
            'discount_type' => 'required|in:1,2',
            'memo_no' => 'required',
        ]);
        if (($request->new_payment ?? 0) > 0) {
            $request->validate([
                'payment_method' => 'required',
            ]);
        }

        // Pre-check stock before starting transaction
        $counter = 0;
        foreach ($request->product_id as $productId) {
            $inventory = Inventory::find($request->inventory_id[$counter]);
            if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                $productName = Product::find($productId)->name ?? 'Product';
                return redirect()->back()->with('error', "Insufficient stock for {$productName} in the selected batch.")->withInput();
            }
            $counter++;
        }

        DB::beginTransaction();
        try {
            $saleOrder = $pre_order;
            $oldOrderNo = $saleOrder->order_no;
            $saleOrder->is_delivered = 1;
            $saleOrder->status = 3; 
            $saleOrder->warehouse_id = $request->warehouse;
            
            $customerType = $saleOrder->customer->type ?? SupplierType::$CUSTOMER;
            if ($customerType == SupplierType::$CORPORATE_CUSTOMER) {
                $saleOrder->type = SaleType::$CORPORATE;
                $prefix = 'CSO-';
            } elseif ($customerType == SupplierType::$DEALER) {
                $saleOrder->type = SaleType::$DEALER;
                $prefix = 'DSO-';
            } else {
                $saleOrder->type = SaleType::$GENERAL;
                $prefix = 'SO-';
            }

            $saleOrder->order_no = generateOrderNo('sale_orders', 'order_no', $prefix);
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->total = $request->total_receivable;
            $saleOrder->advance_adjust = $request->advance_amount ?? 0;
            $saleOrder->paid = $request->total_paid ?? 0;
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->notes = "Pre-Order (Original No: {$oldOrderNo}) delivered. " . ($request->notes ?? '');
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->save();

            SaleOrderItem::where('sale_order_id', $saleOrder->id)->delete();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->product_id as $productId) {
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                    throw new \Exception("Insufficient stock for one or more items.");
                }

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $productId;
                $saleItem->product_code = $request->product_code[$counter] ?? null;
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();

                $inventory->decrement('quantity', $request->product_quantity[$counter]);
                $this->inventoryLog($saleOrder, $inventory, $request->product_quantity[$counter], $request->product_unit_price[$counter], $request->product_code[$counter]);

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $counter++;
            }

            $paymentMethod = $request->payment_method;
            if (!$paymentMethod) {
                $oldPaymentTransaction = Transaction::where('sale_order_id', $saleOrder->id)
                    ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->first();
                $paymentMethod = $oldPaymentTransaction->account_head_id ?? null;
            }

            // Only delete Journal Vouchers for the sale (to recreate with final quantity/amount)
            // We keep the old collection vouchers (Advance payments)
            Voucher::where('sale_order_id', $saleOrder->id)->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)->delete();
            Transaction::where('sale_order_id', $saleOrder->id)->where('voucher_type', VoucherType::$JOURNAL_VOUCHER)->delete();

            // Create Journal Entries for the final sale (delivering)
            $this->createJournalEntries($saleOrder, $costingTotal, false);

            // If there is a new payment during delivery, record it
            if ($request->new_payment > 0) {
                $this->createPaymentEntries($saleOrder, $request->new_payment, $request->payment_method, $request->date, PaymentPurpose::$INSTANT_COLLECTION);
            }

            DB::commit();
            return redirect()->route('pre-orders.index')->with('success', 'Pre-Order delivered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function createJournalEntries($saleOrder, $costingTotal, $isBooking = false)
    {
        $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
        $payeeId = $customerAccountHead->id;

        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
        $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = new Voucher();
        $voucher->sister_concern_id = auth()->user()->activeConcernId();
        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $voucher->amount = $saleOrder->total + $saleOrder->discount + $costingTotal;
        $voucher->date = $saleOrder->date->format('Y-m-d');
        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
        $voucher->voucher_no = $voucherNo;
        $voucher->account_head_payee_depositor_id = $payeeId;
        $voucher->sale_order_id = $saleOrder->id;
        $voucher->notes = "Journal Entry for Pre-Order Delivery #{$saleOrder->order_no}. " . ($saleOrder->notes ?? '');
        $voucher->user_id = auth()->id();
        $voucher->save();

        $this->transaction($voucher, $payeeId, TransactionType::$DEBIT, $saleOrder->total, $saleOrder->id);
        
        if ($isBooking) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::CUSTOMER_ADVANCE_RECEIVED), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id);
        } else {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id);
        }

        if ($saleOrder->discount > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_DISCOUNT), TransactionType::$DEBIT, $saleOrder->discount, $saleOrder->id);
        }

        if ($costingTotal > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), TransactionType::$DEBIT, $costingTotal, $saleOrder->id);
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), TransactionType::$CREDIT, $costingTotal, $saleOrder->id);
        }
    }

    private function createPaymentEntries($saleOrder, $amount, $paymentMethod, $date, $paymentPurpose = null)
    {
        $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
        $payeeId = $customerAccountHead->id;

        $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
        $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
        $pVoucher = new Voucher();
        $pVoucher->sister_concern_id = auth()->user()->activeConcernId();
        $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
        $pVoucher->payment_type_id = AccountHead::find($paymentMethod)->payment_mode ?? null;
        $pVoucher->payment_account_head_id = $paymentMethod; // Receipt Mode
        $pVoucher->amount = $amount;
        $pVoucher->date = Carbon::parse($date)->format('Y-m-d');
        $pVoucher->voucher_no_group_sl = $pNoGroupSl;
        $pVoucher->voucher_no = $pNo;
        $pVoucher->account_head_payee_depositor_id = $payeeId;
        $pVoucher->sale_order_id = null;
        $orderTypeName = ($paymentPurpose == PaymentPurpose::$ADVANCE_COLLECTION) ? "Pre-Order Booking" : "Order";
        $paymentTypeText = ($paymentPurpose == PaymentPurpose::$ADVANCE_COLLECTION) ? "Advance payment received" : "Payment received";
        $pVoucher->notes = "{$paymentTypeText} against {$orderTypeName} #{$saleOrder->order_no}. " . ($saleOrder->notes ?? '');
        $pVoucher->payment_purpose = $paymentPurpose;
        $pVoucher->rm_no = request('rm_no');
        $pVoucher->user_id = auth()->id();
        $pVoucher->save();

        $this->transaction($pVoucher, $paymentMethod, TransactionType::$DEBIT, $amount, null, $paymentPurpose);
        $this->transaction($pVoucher, $payeeId, TransactionType::$CREDIT, $amount, null, $paymentPurpose);
    }

    private function createAccountingEntries($saleOrder, $costingTotal, $request, $paymentMethod = null, $isBooking = false)
    {
        if (!$isBooking) {
            $this->createJournalEntries($saleOrder, $costingTotal, $isBooking);
        }
        
        $paymentAmount = $isBooking ? ($request->paid_amount ?? 0) : $saleOrder->paid;

        if ($paymentAmount > 0) {
            if ($request->advance_adjust != 1) {
                $paymentPurpose = $isBooking ? PaymentPurpose::$ADVANCE_COLLECTION : PaymentPurpose::$INSTANT_COLLECTION;
                $this->createPaymentEntries($saleOrder, $paymentAmount, $paymentMethod, $saleOrder->date, $paymentPurpose);
            }
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

    private function inventoryLog($saleOrder, $inventory, $qty, $sellingPrice, $code)
    {
        $log = new InventoryLog();
        $log->type = 2;
        $log->sister_concern_id = auth()->user()->activeConcernId();
        $log->inventory_id = $inventory->id;
        $log->supplier_id = $saleOrder->customer_id;
        $log->sale_order_id = $saleOrder->id;
        $log->product_id = $inventory->product_id;
        $log->quantity = $qty;
        $log->unit_price = $sellingPrice;
        $log->product_code = $code;
        $log->user_id = auth()->id();
        $log->date = $saleOrder->date;
        $log->save();
    }

    public function printInvoice(SaleOrder $pre_order)
    {
        if ($pre_order->type != SaleType::$PRE_ORDER) abort(404);
        $pre_order->load('saleOrderItems.product.unit', 'saleOrderItems.inventory', 'customer', 'user');

        // Group items for Customer Copy
        // If delivered, we can show batches. If booked, batches might not exist yet.
        $groupedItems = $pre_order->saleOrderItems->groupBy(function($item) {
            return $item->product_id . '-' . $item->selling_price;
        })->map(function ($items) {
            $firstItem = $items->first();
            return (object) [
                'product' => $firstItem->product,
                'product_code' => $firstItem->product_code,
                'total_quantity' => $items->sum('quantity'),
                'selling_price' => $firstItem->selling_price,
                'total_price' => $items->sum(function($item) {
                    return $item->quantity * $item->selling_price;
                }),
                'batches' => $items->filter(function($item) {
                    return $item->inventory_id != null;
                })->map(function($item) {
                    return 'Batch-'.$item->inventory->batch_no;
                })->unique()->implode(', '),
            ];
        });

        return view('sales.pre_order.print', compact('pre_order', 'groupedItems'));
    }

    public function destroy(SaleOrder $pre_order)
    {
        if (!auth()->user()->hasPermissionTo('pre_orders_delete')) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // $hasLaterPayments = \App\Models\Account\Voucher::where('sale_order_id', $pre_order->id)
            //     ->whereHas('transactions', function($q) {
            //         $q->whereNull('sale_order_id');
            //     })->exists();

            // if ($hasLaterPayments) {
            //     return response()->json(['success' => false, 'message' => 'Cannot delete: Invoice has subsequent Customer Payments. Please delete the payments first.'], 200);
            // }

            if ($pre_order->is_delivered == 1) {
                $saleItems = SaleOrderItem::where('sale_order_id', $pre_order->id)->get();
                foreach ($saleItems as $saleItem) {
                    $inventory = Inventory::where('id', $saleItem->inventory_id)->first();
                    if ($inventory) {
                        $inventory->increment('quantity', $saleItem->quantity);
                    }
                }
            }

            SaleOrderItem::where('sale_order_id', $pre_order->id)->delete();
            InventoryLog::where('sale_order_id', $pre_order->id)->delete();
            Voucher::where('sale_order_id', $pre_order->id)->delete();
            Transaction::where('sale_order_id', $pre_order->id)->delete();

            $pre_order->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Successfully Deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 200);
        }
    }
}

