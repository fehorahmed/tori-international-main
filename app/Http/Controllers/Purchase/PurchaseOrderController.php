<?php

namespace App\Http\Controllers\Purchase;

use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\SupplierType;
use App\Enumeration\Status;
use App\Http\Controllers\Controller;
use App\Enumeration\PaymentPurpose;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountHead;
use App\Models\Division;
use App\Rules\BusinessDate;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
            ->get();
        $suppliers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)
            ->where('type', SupplierType::$SUPPLIER)
            ->get();
        $divisions = Division::orderBy('name')->get();
        return view('purchase.index', compact('paymentModes','suppliers', 'divisions'));
    }

    public function details(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_list')) {
            abort(403, 'Unauthorized');
        }

        $purchase->load('purchaseItems');
        return view('purchase.details', compact('purchase'));
    }

    public function detailsPrint(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_list')) {
            abort(403, 'Unauthorized');
        }

        $purchase->load('purchaseItems');
        return view('purchase.details_print', compact('purchase'));
    }

    public function dataTable()
    {
        $query = PurchaseOrder::with('supplier')->where('sister_concern_id',auth()->user()->activeConcernId())->orderBy('id','desc');
        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('supplier') && request('supplier') != '') {
            $query->where('supplier_id', request('supplier'));
        }
        if (request()->has('division_id') && request('division_id') != '') {
            $query->whereHas('supplier', function ($q) {
                $q->where('division_id', request('division_id'));
            });
        }
        if (request()->has('district_id') && request('district_id') != '') {
            $query->whereHas('supplier', function ($q) {
                $q->where('district_id', request('district_id'));
            });
        }
        if (request()->has('upazila_id') && request('upazila_id') != '') {
            $query->whereHas('supplier', function ($q) {
                $q->where('upazila_id', request('upazila_id'));
            });
        }
        if (request()->has('union_id') && request('union_id') != '') {
            $query->whereHas('supplier', function ($q) {
                if (strpos(request('union_id'), 'union_') !== false) {
                    $q->where('union_id', str_replace('union_', '', request('union_id')));
                } elseif (strpos(request('union_id'), 'pourashava_') !== false) {
                    $q->where('pourashava_id', str_replace('pourashava_', '', request('union_id')));
                }
            });
        }
        if (request()->has('village_id') && request('village_id') != '') {
            $query->whereHas('supplier', function ($q) {
                $q->where('village_id', request('village_id'));
            });
        }
        return DataTables::eloquent($query)
            ->addColumn('action', function (PurchaseOrder $order) {
                $btn = '';
                // if (auth()->user()->hasPermissionTo('purchase_payment')) {
                //     if ($order->due > 0)
                //         $btn .= '<a role="button" data-id="' . $order->id . '" data-advance="' . ($order->supplier->outstanding_balance ?? 0) . '" data-total="' . $order->total . '" data-due="' . $order->due . '" data-order_no="' . $order->order_no . '" class="btn btn-info bg-gradient-info btn-sm supplier-pay">Pay</a>';
                // }
                // if (auth()->user()->hasPermissionTo('purchase_edit')) {
                    // $requisitionType = $order->purchase_requisition_id ? 1 : 2;
                    // $btn .= ' <a href="' . route('purchase.edit', ['purchase' => $order->id, 'requisition_type' => $requisitionType]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-edit"></i></a>';
                // }
                if (auth()->user()->hasPermissionTo('purchase_list')) {
                    $btn .= ' <a href="' . route('purchase.details', ['purchase' => $order->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('purchase_list') && in_array($order->status,[Status::PARTIAL_RECEIVED,Status::RECEIVED])) {
                    // $btn .= ' <a href="' . route('purchase.mrr_details', ['purchase' => $order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm">MRR <i class="fa fa-info-circle"></i></a>';
                }
                if (auth()->user()->can('purchase_delete') && in_array($order->status,[Status::PENDING,Status::PARTIAL_RECEIVED])) {
                    // $btn .= ' <a href="' . route('purchase.received', ['purchase' => $order->id]) . '" class="btn btn-success bg-gradient-success btn-sm">Receive</a>';
                }
                if (auth()->user()->can('purchase_delete')) {
                    $btn .= ' <a role="button" data-id="' . $order->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return dropdownMenuContainer($btn);
            })

            ->editColumn('status', function (PurchaseOrder $order) {

                $classStatus = get_status_badge_class($order->status);
                return '<span class="badge '.$classStatus.'">'.ucfirst(str_replace('_',' ',$order->status)).'</span>';
            })
            ->addColumn('supplier_name', function (PurchaseOrder $order) {
                return $order->supplier->name ?? '';
            })
            ->rawColumns(['action','status'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_create')) {
            abort(403, 'Unauthorized');
        }
        $suppliers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->where('type',1)
            ->get();
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
            ->get();
        $products = []; // Products will be loaded via AJAX

        $warehouses = Warehouse::where('sister_concern_id',auth()->user()->activeConcernId())->where('type', 'main')->get();

        return view('purchase.create', compact('suppliers','paymentModes','products', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $rules = [
            'warehouse' => 'required',
            'supplier' => 'required',
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric|min:0.001',
            'product_selling_price.*' => 'required|numeric|min:0.001',
            'product_wholesale_price.*' => 'required|numeric|min:0',
            'sub_total' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'vat_percent' => 'nullable|numeric',
            'vat_amount' => 'nullable|numeric',
            'tax_percent' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'total_payable' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => Rule::requiredIf($request->paid_amount > 0),
            'date' => ['required', 'date', new BusinessDate()],
            'notes' => 'nullable|string|max:255',
        ];
        $request->validate($rules);

        if ($request->paid_amount > 0) {
            $paymentAccount = AccountHead::find($request->payment_method);
            // if (!$paymentAccount->isSufficient($request->paid_amount)) {
            //     return redirect()->back()->withInput()->with('error', "Insufficient balance in {$paymentAccount->name}. Current balance is " . number_format($paymentAccount->current_balance, 2));
            // }
        }

        // Start a database transaction
        DB::beginTransaction();

        try {

            $purchase = new PurchaseOrder();
            $purchase->order_no = generateOrderNo('purchase_orders', 'order_no', 'PO-');
            $purchase->sister_concern_id = auth()->user()->activeConcernId();
            $purchase->warehouse_id = $request->warehouse;
            $purchase->supplier_id = $request->supplier;
            $purchase->discount_type = $request->discount_type ?? 2;
            $purchase->discount_percent = $request->discount_percent ?? 0;
            $purchase->discount_amount = $request->discount_amount ?? 0;
            $purchase->vat_percent = $request->vat_percent ?? 0;
            $purchase->vat_amount = $request->vat_amount ?? 0;
            $purchase->tax_percent = $request->tax_percent ?? 0;
            $purchase->tax_amount = $request->tax_amount ?? 0;
            $purchase->sub_total = $request->sub_total;
            $purchase->total = $request->total_payable;
            $purchase->advance_adjust = $request->advance_amount ?? 0;
            $purchase->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $purchase->due = $request->total_payable - $purchase->paid;
            $purchase->notes = $request->notes;
            $purchase->user_id = auth()->id();
            $purchase->date = Carbon::parse($request->date);
            $purchase->status = Status::RECEIVED;
            $purchase->save(); 

            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);

                $productCode = $product->code;

                //Inventory
                $inventory = Inventory::where('sister_concern_id',auth()->user()->activeConcernId())
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->where('unit_price', $request->product_unit_price[$key])
                    ->first();

                $batch_no = null;    
                if (!$inventory) {
                    $latest_inventory = Inventory::where('sister_concern_id',auth()->user()->activeConcernId())
                        ->where('product_id', $product->id)
                        ->where('warehouse_id', $purchase->warehouse_id)
                        ->latest('id')
                        ->first();

                    $batch_no = $latest_inventory ? intval($latest_inventory->batch_no) + 1 : 1;    

                    $inventory = new Inventory();
                    $inventory->sister_concern_id = auth()->user()->activeConcernId();
                    $inventory->inventory_type = 1;
                    $inventory->warehouse_id = $purchase->warehouse_id;
                    $inventory->product_id = $product->id;
                    $inventory->batch_no = $batch_no;
                    $inventory->product_code = $productCode;
                    $inventory->quantity = $request->product_qty[$key];
                    $inventory->unit_price = $request->product_unit_price[$key];
                    $inventory->save();
                } else {
                    $batch_no = $inventory->batch_no;

                    $inventory->unit_price = $request->product_unit_price[$key];
                    $inventory->save();
                    $inventory->increment('quantity', $request->product_qty[$key]);
                }

                $purchaseItem = new PurchaseOrderItem();
                $purchaseItem->sister_concern_id = auth()->user()->activeConcernId();
                $purchaseItem->purchase_order_id = $purchase->id;
                $purchaseItem->product_code = $productCode;
                $purchaseItem->product_id = $product->id;
                $purchaseItem->quantity = $request->product_qty[$key];
                $purchaseItem->received_quantity = $request->product_qty[$key];
                $purchaseItem->unit_price = $request->product_unit_price[$key];
                $purchaseItem->batch_no = $batch_no;
                $purchaseItem->save();
                
                // Update Product Purchase Price
                $product->purchase_price = $purchaseItem->unit_price;
                $product->save();

                //Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->type = InventoryLogType::$PURCHASE_IN;
                $inventoryLog->warehouse_id = $purchase->warehouse_id;
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $purchase->supplier_id;
                $inventoryLog->purchase_order_id = $purchase->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->batch_no = $batch_no;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $purchaseItem->unit_price;
                $inventoryLog->product_code = $productCode;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();
                $total += ($request->product_qty[$key] * $purchaseItem->unit_price);
            }

            $purchase->sub_total = $total;
            
            // Re-calculate discount based on new total if percentage
            if ($request->discount_type == 2 && $purchase->discount_percent > 0) {
                $purchase->discount_amount = ($total / 100) * $purchase->discount_percent;
            } else if ($request->discount_type == 1) {
                $purchase->discount_amount = $request->discount_percent ?? 0;
                $purchase->discount_percent = 0; // Clear percent for flat
            }

            $purchase->total = ceil(($total - $purchase->discount_amount) + ($request->vat_amount ?? 0) + ($request->tax_amount ?? 0));
            $purchase->advance_adjust = $request->advance_amount ?? 0;
            $purchase->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $purchase->due = $purchase->total - $purchase->paid;
            $purchase->save();
            
            $vatAmount = $request->vat_amount ?? 0;
            $discountAmount = $purchase->discount_amount;

            if ($total > 0) {
                $supplierName = $purchase->supplier->name ?? 'Supplier';
                $customNote = $request->notes ? " Note: " . $request->notes : "";
                $smartNote = "Purchased from {$supplierName} against PO no - {$purchase->order_no}.{$customNote}";

                //Journal Voucher
                $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $purchase->supplier_id)->first()->id ?? null;

                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $total + $vatAmount;
                $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->voucher_no = $voucherNo;
                $voucher->account_head_payee_depositor_id = $payeeId;
                $voucher->purchase_order_id = $purchase->id;
                $voucher->notes = $smartNote;
                $voucher->user_id = auth()->id();
                $voucher->save();

                //Debit Inventory
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $total;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = $smartNote;
                $transaction->user_id = auth()->id();
                $transaction->save();

                //Credit Supplier (Accounts Payable)
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $payeeId;
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$CREDIT;
                $transaction->amount = $total + $vatAmount - $discountAmount;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = $smartNote;
                $transaction->user_id = auth()->id();
                $transaction->save();

                if ($vatAmount > 0) {
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::VAT_EXPENSE);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $vatAmount;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $payeeId;
                    $transaction->purchase_order_id = $purchase->id;
                    $transaction->notes = 'VAT on purchase order no - '.$purchase->order_no;
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }

                if ($discountAmount > 0) {
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::PURCHASE_DISCOUNT);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $discountAmount;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $payeeId;
                    $transaction->purchase_order_id = $purchase->id;
                    $transaction->notes = 'Purchase discount received against purchase order no - '.$purchase->order_no;
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }
            }

            if ($request->paid_amount > 0) {
                $paymentMode = $request->payment_method;
                $paymentAccount = AccountHead::find($paymentMode);

                $paymentModeType = $paymentAccount->payment_mode;

                $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
                $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $purchase->supplier_id)->first()->id ?? null;

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->payment_type_id = $paymentModeType;
                $voucher->payment_account_head_id = $paymentMode;//Payment Mode
                $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $voucher->amount = $request->paid_amount;
                $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->voucher_no = $voucherNo;
                $voucher->account_head_payee_depositor_id = $payeeId;
                $voucher->purchase_order_id = $purchase->id;
                $voucher->notes = 'Payment against purchase order no - '.$purchase->order_no;
                $voucher->payment_purpose = PaymentPurpose::$INSTANT_PAYMENT;
                $voucher->user_id = auth()->id();
                $voucher->save();

                //Debit
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->payment_type_id = $paymentModeType;
                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                $transaction->account_head_id = $payeeId;//Liabilities: Decrease in Accounts Payable
                $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $request->paid_amount;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = 'Payment against purchase order no - '.$purchase->order_no;
                $transaction->payment_purpose = PaymentPurpose::$INSTANT_PAYMENT;
                $transaction->user_id = auth()->id();
                $transaction->save();

                //Credit
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->payment_type_id = $paymentModeType;
                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                $transaction->account_head_id = $paymentMode;//Assets: Decrease in Cash/Bank
                $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $transaction->transaction_type = TransactionType::$CREDIT;
                $transaction->amount = $request->paid_amount;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = 'Payment against purchase order no - '.$purchase->order_no;
                $transaction->payment_purpose = PaymentPurpose::$INSTANT_PAYMENT;
                $transaction->user_id = auth()->id();
                $transaction->save();
            }
            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('purchase.details', ['purchase' => $purchase->id])->with('success', 'Purchase created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->withInput()->with('error', $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($purchase->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }

        $suppliers = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->get();
        $products = Product::where('status', 1)
            ->get();
        $warehouses = Warehouse::where('sister_concern_id',auth()->user()->activeConcernId())->where('type', 'main')->get();

        return view('purchase.edit', compact('suppliers'
            , 'products', 'purchase', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchase)
    {

        if (!auth()->user()->hasPermissionTo('purchase_edit')) {
            abort(403, 'Unauthorized');
        }else{
            if($purchase->sister_concern_id != auth()->user()->activeConcernId()){
                abort(403, 'Unauthorized');
            }
        }
        if ($purchase->status != Status::PENDING) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $rules = [
            'warehouse' => 'required',
            'supplier' => 'required',
            'product_id.*' => 'required',
            'product_qty.*' => 'required|numeric',
            'product_unit_price.*' => 'required|numeric|min:0.001',
            'sub_total' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'vat_percent' => 'nullable|numeric',
            'vat_amount' => 'nullable|numeric',
            'tax_percent' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'total_payable' => 'required|numeric|min:0',
            'date' => ['required', 'date', new BusinessDate()],
            'notes' => 'nullable|string|max:255',
        ];

        $request->validate($rules);

        if ($request->paid_amount > 0) {
            $paymentAccount = AccountHead::find($request->payment_method);
            // if (!$paymentAccount->isSufficient($request->paid_amount)) {
            //     return redirect()->back()->withInput()->with('error', "Insufficient balance in {$paymentAccount->name}. Current balance is " . number_format($paymentAccount->current_balance, 2));
            // }
        }


        // Start a database transaction
        DB::beginTransaction();

        try {



            // Stock Reset
            $oldLogs = InventoryLog::where('purchase_order_id', $purchase->id)->get();
            foreach($oldLogs as $log) {
                $inventory = Inventory::find($log->inventory_id);
                if ($inventory) {
                    if ($inventory->quantity < $log->quantity) {
                        throw new \Exception("Stock has been utilized for product. Cannot update.");
                    }
                    $inventory->decrement('quantity', $log->quantity);
                }
            }
            InventoryLog::where('purchase_order_id', $purchase->id)->delete();
            PurchaseOrderItem::where('purchase_order_id', $purchase->id)->delete();
            Voucher::where('purchase_order_id', $purchase->id)->delete();
            Transaction::where('purchase_order_id', $purchase->id)->delete();

            $purchase->warehouse_id = $request->warehouse;
            $purchase->supplier_id = $request->supplier;
            $purchase->sub_total = $request->sub_total;
            $purchase->discount_type = $request->discount_type ?? 2;
            $purchase->discount_percent = $request->discount_percent ?? 0;
            $purchase->discount_amount = $request->discount_amount ?? 0;
            $purchase->vat_percent = $request->vat_percent ?? 0;
            $purchase->vat_amount = $request->vat_amount ?? 0;
            $purchase->tax_percent = $request->tax_percent ?? 0;
            $purchase->tax_amount = $request->tax_amount ?? 0;
            $purchase->total = $request->total_payable;
            $purchase->paid = 0;
            $purchase->due = $request->total_payable;
            $purchase->notes = $request->notes;
            $purchase->user_id = auth()->id();
            $purchase->date = Carbon::parse($request->date);
            $purchase->save();

            $total = 0;
            foreach ($request->product_id as $key => $productId) {
                $product = Product::find($request->product_id[$key]);
                $productCode = $product->code;

                //Inventory
                $inventory = Inventory::where('sister_concern_id',auth()->user()->activeConcernId())
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->where('unit_price', $request->product_unit_price[$key])
                    ->first();

                $batch_no = null;    
                if (!$inventory) {
                    $latest_inventory = Inventory::where('sister_concern_id',auth()->user()->activeConcernId())
                        ->where('product_id', $product->id)
                        ->where('warehouse_id', $purchase->warehouse_id)
                        ->latest('id')
                        ->first();

                    $batch_no = $latest_inventory ? intval($latest_inventory->batch_no) + 1 : 1;    

                    $inventory = new Inventory();
                    $inventory->sister_concern_id = auth()->user()->activeConcernId();
                    $inventory->inventory_type = 1;
                    $inventory->warehouse_id = $purchase->warehouse_id;
                    $inventory->product_id = $product->id;
                    $inventory->batch_no = $batch_no;
                    $inventory->product_code = $productCode;
                    $inventory->quantity = $request->product_qty[$key];
                    $inventory->unit_price = $request->product_unit_price[$key];
                    $inventory->save();
                } else {
                    $batch_no = $inventory->batch_no;

                    $inventory->unit_price = $request->product_unit_price[$key];
                    $inventory->save();
                    $inventory->increment('quantity', $request->product_qty[$key]);
                }

                $purchaseItem = new PurchaseOrderItem();
                $purchaseItem->sister_concern_id = auth()->user()->activeConcernId();
                $purchaseItem->purchase_order_id = $purchase->id;
                $purchaseItem->product_code = $productCode;
                $purchaseItem->product_id = $product->id;
                $purchaseItem->quantity = $request->product_qty[$key];
                $purchaseItem->received_quantity = $request->product_qty[$key];
                $purchaseItem->unit_price = $request->product_unit_price[$key];
                $purchaseItem->batch_no = $batch_no;
                $purchaseItem->save();

                // Update Product Purchase Price
                $product->purchase_price = $purchaseItem->unit_price;
                $product->save();

                //Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->sister_concern_id = auth()->user()->activeConcernId();
                $inventoryLog->type = InventoryLogType::$PURCHASE_IN;
                $inventoryLog->warehouse_id = $purchase->warehouse_id;
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->supplier_id = $purchase->supplier_id;
                $inventoryLog->purchase_order_id = $purchase->id;
                $inventoryLog->product_id = $product->id;
                $inventoryLog->batch_no = $batch_no;
                $inventoryLog->quantity = $request->product_qty[$key];
                $inventoryLog->unit_price = $purchaseItem->unit_price;

                $inventoryLog->product_code = $productCode;
                $inventoryLog->notes = $request->notes;
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->date = Carbon::parse($request->date);
                $inventoryLog->save();
                $total += ($request->product_qty[$key] * $purchaseItem->unit_price);
            }

            $purchase->sub_total = $total;
            
            // Re-calculate discount based on new total if percentage
            if ($request->discount_type == 2 && $purchase->discount_percent > 0) {
                $purchase->discount_amount = ($total / 100) * $purchase->discount_percent;
            } else if ($request->discount_type == 1) {
                $purchase->discount_amount = $request->discount_percent ?? 0;
                $purchase->discount_percent = 0; // Clear percent for flat
            }

            $purchase->total = ceil(($total - $purchase->discount_amount) + ($request->vat_amount ?? 0) + ($request->tax_amount ?? 0));
            // $purchase->paid = 0;
            $purchase->due = $purchase->total;
            $purchase->save();
            
            $vatAmount = $request->vat_amount ?? 0;
            $discountAmount = $purchase->discount_amount;

            if ($total > 0) {
                //Journal Voucher
                $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $purchase->supplier_id)->first()->id ?? null;

                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $total + $vatAmount;
                $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->voucher_no = $voucherNo;
                $voucher->account_head_payee_depositor_id = $payeeId;
                $voucher->purchase_order_id = $purchase->id;
                $voucher->notes = $request->notes;
                $voucher->user_id = auth()->id();
                $voucher->save();

                //Debit Inventory
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $total;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = $request->notes;
                $transaction->user_id = auth()->id();
                $transaction->save();

                //Credit Supplier (Accounts Payable)
                $transaction = new Transaction();
                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $payeeId;
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$CREDIT;
                $transaction->amount = $total + $vatAmount - $discountAmount;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                $transaction->voucher_no = $voucherNo;
                $transaction->account_head_payee_depositor_id = $payeeId;
                $transaction->purchase_order_id = $purchase->id;
                $transaction->notes = $request->notes;
                $transaction->user_id = auth()->id();
                $transaction->save();

                if ($vatAmount > 0) {
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::VAT_EXPENSE);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $vatAmount;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $payeeId;
                    $transaction->purchase_order_id = $purchase->id;
                    $transaction->notes = 'VAT on purchase';
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }

                if ($discountAmount > 0) {
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::PURCHASE_DISCOUNT);
                    $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $discountAmount;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $payeeId;
                    $transaction->purchase_order_id = $purchase->id;
                    $transaction->notes = 'Purchase discount received';
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }
            }
            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('purchase.details', ['purchase' => $purchase->id])->with('success', 'Purchase updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()->withInput()
                ->withInput()->with('error', $e->getMessage().' '.$e->getLine());
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(PurchaseOrder $purchase)
    {
        if (!auth()->user()->hasPermissionTo('purchase_delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], Response::HTTP_OK);
        }

        // Check product stock before beginning transaction for better performance
        foreach ($purchase->purchaseItems as $purchaseItem) {
            $inventory = Inventory::where('product_id', $purchaseItem->product_id)
                ->where('sister_concern_id', $purchase->sister_concern_id)
                ->where('warehouse_id', $purchase->warehouse_id)
                ->where('unit_price', $purchaseItem->unit_price)
                ->first();

            if (!$inventory) {
                $inventory = Inventory::where('product_id', $purchaseItem->product_id)
                    ->where('sister_concern_id', $purchase->sister_concern_id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->first();
            }

            if ($inventory && $inventory->quantity < $purchaseItem->quantity) {
                return response()->json([
                    'success' => false, 
                    'message' => ($inventory->product->name ?? 'Product') . " has insufficient stock: " . $inventory->quantity
                ], Response::HTTP_OK);
            }
        }

        DB::beginTransaction();
        try {
            foreach ($purchase->purchaseItems as $purchaseItem) {
                $inventory = Inventory::where('product_id', $purchaseItem->product_id)
                    ->where('sister_concern_id', $purchase->sister_concern_id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->where('unit_price', $purchaseItem->unit_price)
                    ->first();

                if (!$inventory) {
                    $inventory = Inventory::where('product_id', $purchaseItem->product_id)
                        ->where('sister_concern_id', $purchase->sister_concern_id)
                        ->where('warehouse_id', $purchase->warehouse_id)
                        ->first();
                }

                if ($inventory) {
                    $inventory->decrement('quantity', $purchaseItem->quantity);
                }
            }

            // Delete all associated records to ensure database integrity
            PurchaseOrderItem::where('purchase_order_id', $purchase->id)->delete();
            InventoryLog::where('purchase_order_id', $purchase->id)->delete();
            
            // Delete associated vouchers and transactions
            Voucher::where('purchase_order_id', $purchase->id)->delete();
            Transaction::where('purchase_order_id', $purchase->id)->delete();

            $purchase->delete();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Purchase order deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }
    }

    public function payment(Request $request)
    {

        // Validate the request data
        $rules = [
            'order_no' => 'required',
            'payment_mode' => 'required',
            'payment' => 'required|numeric|min:.01|max:' . $request->due_hidden,
            'due' => 'required|numeric|min:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ];
        $paymentMode = 0;
        $paymentModeType = 0;
        if ($request->payment_mode != '') {
            [$paymentMode, $paymentModeType] = explode('|', $request->payment_mode);
        }
        if ($paymentModeType == 1) {
            //bank
            $rules['cheque_no'] = 'nullable|max:255';
        }
        $request->validate($rules);

        $paymentAccount = AccountHead::find($paymentMode);
        if (!$paymentAccount->isSufficient($request->payment)) {
            return response()->json(['success' => false, 'message' => "Insufficient balance in {$paymentAccount->name}. Current balance is " . number_format($paymentAccount->current_balance, 2)]);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasPermissionTo('purchase_payment')) {
                abort(403, 'Unauthorized');
            }
            $purchaseOrder = PurchaseOrder::find($request->order_id);

            $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $purchaseOrder->increment('paid', $request->payment);
            $purchaseOrder->decrement('due', $request->payment);

            $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $purchaseOrder->supplier_id)->first()->id ?? null;

            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $voucher->amount = $request->payment;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->purchase_order_id = $purchaseOrder->id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = new Transaction();
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $payeeId;//Liabilities: Decrease in Accounts Payable
            $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->purchase_order_id = $purchaseOrder->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = new Transaction();
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $paymentMode;//Assets: Decrease in Cash/Bank
            $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->purchase_order_id = $purchaseOrder->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
            $transaction->user_id = auth()->id();
            $transaction->save();

            // Commit the transaction
            DB::commit();
            $message = 'Supplier payment successful';
            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('purchase.index'),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the supplier payment :' . $e->getMessage(),
            ]);
        }
    }

    public function supplierPayments()
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) abort(403);
        $paymentModes = AccountHead::where('payment_mode', '>', 0)
            ->get();
        $divisions = Division::orderBy('name')->get();
        return view('purchase.supplier_payments', compact('paymentModes', 'divisions'));
    }

    public function supplierPaymentsDatatable(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) abort(403);
        $query = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->withSum('purchaseOrders', 'total')
            ->withSum('purchaseOrders', 'paid')
            ->withSum('purchaseOrders', 'due');

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('opening_balance', function(Supplier $supplier) {
                $balance = $supplier->opening_balance;
                if($balance > 0) {
                    return '<span class="badge badge-danger">Due: ' . number_format($balance, 2) . '</span>';
                } elseif($balance < 0) {
                    return '<span class="badge badge-success">Advance: ' . number_format(abs($balance), 2) . '</span>';
                } else {
                    return '<span class="badge badge-secondary">0.00</span>';
                }
            })
            ->addColumn('total', function (Supplier $supplier) {
                return $supplier->purchase_orders_sum_total ?? 0;
            })
            ->addColumn('receive', function (Supplier $supplier) {
                return number_format($supplier->total_supplier_received, 2);
            })
            ->addColumn('paid', function (Supplier $supplier) {
                return number_format($supplier->total_supplier_paid, 2);
            })
            ->addColumn('current_balance', function(Supplier $supplier) {
                $balance = $supplier->supplier_current_balance;
                
                if($balance > 0) {
                    return '<span class="badge badge-danger">Due: ' . number_format($balance, 2) . '</span>';
                } elseif($balance < 0) {
                    return '<span class="badge badge-success">Advance: ' . number_format(abs($balance), 2) . '</span>';
                } else {
                    return '<span class="badge badge-secondary">Cleared</span>';
                }
            })
            ->addColumn('action', function (Supplier $supplier) {
                $html = '';
                $html .= '<button class="btn btn-info bg-gradient-info btn-sm supplier-pay mr-1 mb-1" data-id="' . $supplier->id . '" data-name="' . $supplier->name . '">Payment</button>';
                $html .= '<button class="btn btn-success bg-gradient-success btn-sm supplier-receipt mr-1" data-id="' . $supplier->id . '" data-name="' . $supplier->name . '" title="Receive from Supplier"><i class="fas fa-hand-holding-usd"></i> Receive</button> ';
                $html .= '<a href="' . route('purchase.supplier_payment_history', $supplier->id) . '" class="btn btn-info bg-gradient-info btn-sm" title="Payment History"><i class="fa fa-info-circle"></i></a>';
                return dropdownMenuContainer($html);
            })
            ->rawColumns(['current_balance','action', 'opening_balance'])
            ->toJson();
    }

    public function supplierPaymentCreate(Request $request)
    {
        $supplier = Supplier::findOrFail($request->supplier_id);
        
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $supplier->id)->first();
            
        $opening_balance = $supplier->opening_balance ?? 0;
        
        // Calculate paid opening due. We consider transactions that are DEBIT, linked to this account, and have notes 'Opening Due Payment'
        $paid_opening_due = Transaction::where('account_head_id', $accountHead->id ?? 0)
            ->where('transaction_type', TransactionType::$DEBIT)
            ->where('notes', 'Opening Due Payment')
            ->sum('amount');
            
        $remaining_opening_due = max(0, $opening_balance - $paid_opening_due);
        
        // Find unpaid or partially paid purchase orders
        $unpaidOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->where('due', '>', 0)
            ->get();
            
        // Calculate available advance
        $totalPurchase = $supplier->purchase_orders_sum_total ?? PurchaseOrder::where('supplier_id', $supplier->id)->sum('total');
            
        $totalPaid = $supplier->total_supplier_paid;
            
        $balance = $opening_balance + $totalPurchase - $totalPaid;
        $available_advance = $balance < 0 ? abs($balance) : 0;
        
        // Fetch payment modes (Account Heads)
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })
            ->where('payment_mode', '>', 0)
            ->where('status', 1)
            ->get();

        return view('purchase._payment_form', compact(
            'supplier', 'remaining_opening_due', 'unpaidOrders', 'available_advance', 'paymentModes'
        ));
    }

    public function supplierPaymentStore(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'payment_mode' => 'required',
            'total_receive_amount' => 'nullable|numeric|min:0',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        $totalReceive = $request->total_receive_amount ? (float) $request->total_receive_amount : 0;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        $payment_mode_data = explode('|', $request->payment_mode);
        $payment_account_head_id = $payment_mode_data[0];
        $payment_mode_type = $payment_mode_data[1];

        $paymentAccountHead = AccountHead::findOrFail($payment_account_head_id);
        if (!$paymentAccountHead->isSufficient($totalReceive, auth()->user()->activeConcernId())) {
            return response()->json([
                'status' => false,
                'message' => "Insufficient balance in {$paymentAccountHead->name}. Current balance is ৳ " . number_format($paymentAccountHead->getCurrentBalance(auth()->user()->activeConcernId()), 2),
            ]);
        }
        
        DB::beginTransaction();

        try {

            $supplierAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $supplier->id)->first();
            if(!$supplierAccountHead) {
                throw new \Exception("Supplier Account Head not found!");
            }

            // 1. Process Opening Due Payment
            if ($request->allocate_opening_due && $request->allocate_opening_due > 0) {
                $amt = $request->allocate_opening_due;
                
                $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $voucher->amount = $amt;
                $voucher->date = $date;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $supplierAccountHead->id;
                $voucher->payment_type_id = $payment_mode_type;
                $voucher->payment_account_head_id = $payment_account_head_id;
                $voucher->cheque_no = $request->cheque_no;
                $voucher->notes = 'Opening Due Payment' . ($request->note ? ' - ' . $request->note : '');
                $voucher->payment_purpose = PaymentPurpose::$OPENING_DUE_PAYMENT;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // Debit: Supplier
                $debitTx = new Transaction();
                $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                $debitTx->voucher_id = $voucher->id;
                $debitTx->account_head_id = $supplierAccountHead->id;
                $debitTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $debitTx->transaction_type = TransactionType::$DEBIT;
                $debitTx->amount = $amt;
                $debitTx->date = $date;
                $debitTx->voucher_no = $voucherNo;
                $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                $debitTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                $debitTx->notes = 'Opening Due Payment';
                $debitTx->payment_purpose = PaymentPurpose::$OPENING_DUE_PAYMENT;
                $debitTx->user_id = auth()->id();
                $debitTx->save();

                // Credit: Cash/Bank
                $creditTx = new Transaction();
                $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                $creditTx->voucher_id = $voucher->id;
                $creditTx->account_head_id = $payment_account_head_id;
                $creditTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $creditTx->transaction_type = TransactionType::$CREDIT;
                $creditTx->amount = $amt;
                $creditTx->date = $date;
                $creditTx->voucher_no = $voucherNo;
                $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                $creditTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                $creditTx->notes = 'Opening Due Payment' . ($request->note ? ' - ' . $request->note : '');
                $creditTx->payment_purpose = PaymentPurpose::$OPENING_DUE_PAYMENT;
                $creditTx->user_id = auth()->id();
                $creditTx->save();
            }

            // 2. Process Order Due Payments
            if ($request->orders && is_array($request->orders)) {
                foreach ($request->orders as $orderData) {
                    if (!empty($orderData['amount']) && $orderData['amount'] > 0) {
                        $amt = $orderData['amount'];
                        $order = PurchaseOrder::findOrFail($orderData['id']);
                        
                        $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
                        $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                        $voucher = new Voucher();
                        $voucher->sister_concern_id = auth()->user()->activeConcernId();
                        $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                        $voucher->amount = $amt;
                        $voucher->date = $date;
                        $voucher->voucher_no = $voucherNo;
                        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                        $voucher->account_head_payee_depositor_id = $supplierAccountHead->id;
                        $voucher->payment_type_id = $payment_mode_type;
                        $voucher->payment_account_head_id = $payment_account_head_id;
                        $voucher->cheque_no = $request->cheque_no;
                        $voucher->notes = 'Order Due Payment (' . $order->order_no . ')' . ($request->note ? ' - ' . $request->note : '');
                        $voucher->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
                        $voucher->purchase_order_id = $order->id;
                        $voucher->user_id = auth()->id();
                        $voucher->save();

                        // Debit: Supplier
                        $debitTx = new Transaction();
                        $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                        $debitTx->voucher_id = $voucher->id;
                        $debitTx->account_head_id = $supplierAccountHead->id;
                        $debitTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                        $debitTx->transaction_type = TransactionType::$DEBIT;
                        $debitTx->amount = $amt;
                        $debitTx->date = $date;
                        $debitTx->voucher_no = $voucherNo;
                        $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                        $debitTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                        $debitTx->notes = 'Order Due Payment (' . $order->order_no . ')' . ($request->note ? ' - ' . $request->note : '');
                        $debitTx->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
                        $debitTx->purchase_order_id = $order->id;
                        $debitTx->user_id = auth()->id();
                        $debitTx->save();

                        // Credit: Cash/Bank
                        $creditTx = new Transaction();
                        $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                        $creditTx->voucher_id = $voucher->id;
                        $creditTx->account_head_id = $payment_account_head_id;
                        $creditTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                        $creditTx->transaction_type = TransactionType::$CREDIT;
                        $creditTx->amount = $amt;
                        $creditTx->date = $date;
                        $creditTx->voucher_no = $voucherNo;
                        $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                        $creditTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                        $creditTx->notes = 'Order Due Payment (' . $order->order_no . ')' . ($request->note ? ' - ' . $request->note : '');
                        $creditTx->payment_purpose = PaymentPurpose::$ORDER_DUE_PAYMENT;
                        $creditTx->purchase_order_id = $order->id;
                        $creditTx->user_id = auth()->id();
                        $creditTx->save();
                        
                        $order->increment('paid', $amt);
                        $order->decrement('due', $amt);
                    }
                }
            }
            
            // 3. Process Advance Payment
            if ($request->advance_amount && $request->advance_amount > 0) {
                $amt = $request->advance_amount;
                
                $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $voucher->amount = $amt;
                $voucher->date = $date;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $supplierAccountHead->id;
                $voucher->payment_type_id = $payment_mode_type;
                $voucher->payment_account_head_id = $payment_account_head_id;
                $voucher->cheque_no = $request->cheque_no;
                $voucher->notes = 'Advance Payment' . ($request->note ? ' - ' . $request->note : '');
                $voucher->payment_purpose = PaymentPurpose::$ADVANCE_PAYMENT;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // Debit: Supplier
                $debitTx = new Transaction();
                $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                $debitTx->voucher_id = $voucher->id;
                $debitTx->account_head_id = $supplierAccountHead->id;
                $debitTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $debitTx->transaction_type = TransactionType::$DEBIT;
                $debitTx->amount = $amt;
                $debitTx->date = $date;
                $debitTx->voucher_no = $voucherNo;
                $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                $debitTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                $debitTx->notes = 'Advance Payment' . ($request->note ? ' - ' . $request->note : '');
                $debitTx->payment_purpose = PaymentPurpose::$ADVANCE_PAYMENT;
                $debitTx->user_id = auth()->id();
                $debitTx->save();

                // Credit: Cash/Bank
                $creditTx = new Transaction();
                $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                $creditTx->voucher_id = $voucher->id;
                $creditTx->account_head_id = $payment_account_head_id;
                $creditTx->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                $creditTx->transaction_type = TransactionType::$CREDIT;
                $creditTx->amount = $amt;
                $creditTx->date = $date;
                $creditTx->voucher_no = $voucherNo;
                $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                $creditTx->account_head_payee_depositor_id = $supplierAccountHead->id;
                $creditTx->notes = 'Advance Payment' . ($request->note ? ' - ' . $request->note : '');
                $creditTx->payment_purpose = PaymentPurpose::$ADVANCE_PAYMENT;
                $creditTx->user_id = auth()->id();
                $creditTx->save();
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment processed successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage() . ' Line: ' . $e->getLine(),
            ], 422);
        }
    }

    public function supplierDueOrders(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) abort(403);
        $supplierId = $request->supplier_id;
        $orders = PurchaseOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('supplier_id', $supplierId)
            ->where('due', '>', 0)
            ->orderBy('date', 'asc')
            ->get();
            
        return response()->json($orders);
    }

    public function supplierPaymentHistory(Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) abort(403);
        return view('purchase.supplier_payment_history', compact('supplier'));
    }

    public function supplierPaymentHistoryDatatable(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('purchase_payment')) abort(403);
        $supplierId = $request->supplier_id;
        
        $query = Transaction::with(['accountHead.supplier', 'voucher.purchaseOrder', 'voucher.paymentAccountHead'])
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->whereIn('voucher_type', [VoucherType::$PAYMENT_VOUCHER, VoucherType::$JOURNAL_VOUCHER, VoucherType::$COLLECTION_VOUCHER])
            ->whereHas('accountHead', function($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->orderBy('id', 'desc');

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('date', function (Transaction $transaction) {
                return $transaction->date ? Carbon::parse($transaction->date)->format('d-m-Y') : '';
            })
            ->addColumn('payment_type', function (Transaction $transaction) {
                return $transaction->notes ?? '-';
            })
            ->addColumn('payment_mode', function (Transaction $transaction) {
                return $transaction->voucher->paymentAccountHead->name ?? '-';
            })
            ->addColumn('amount', function (Transaction $transaction) {
                $prefix = $transaction->transaction_type == TransactionType::$CREDIT ? '+' : '-';
                $color = $transaction->transaction_type == TransactionType::$CREDIT ? 'text-success' : 'text-danger';
                return '<span class="' . $color . '">' . $prefix . ' ' . number_format($transaction->amount, 2) . '</span>';
            })
            ->addColumn('action', function (Transaction $transaction) {
                if ($transaction->voucher) {
                    $printRoute = route('voucher.print', ['voucher' => $transaction->voucher->id]);
                    return '<a href="' . $printRoute . '" class="btn btn-info btn-sm bg-gradient-info text-white" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
                }
                return '';
            })
            ->rawColumns(['action', 'amount'])
            ->toJson();
    }

    public function supplierValidationCheck(Request $request)
    {
        $supplier = Supplier::find($request->supplier_id);
        if (!$supplier) {
            return response()->json(['current_balance' => 0]);
        }
        
        return response()->json([
            'current_balance' => $supplier->supplier_current_balance
        ]);
    }
}
