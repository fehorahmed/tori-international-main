<?php

namespace App\Http\Controllers\Ecommerce;

use App\Enumeration\ReservedAccountGroup;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Ecommerce\Cart;
use App\Models\Ecommerce\CartItem;
use App\Models\Department;
use App\Models\Ecommerce\EcomOrderInfo;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Settings\Product;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Supplier;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Sales\SalePayment;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\User;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\SaleType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller{
    public function index(){
        $accounts = AccountHead::where('payment_mode', 1)->where('status', 1)->orderBy('name')->get();
        $mobileBankings = AccountHead::where('payment_mode', 3)->where('status', 1)->orderBy('name')->get();
        return view('ecommerce.order.all', compact('accounts', 'mobileBankings'));
    }

    public function datatable(Request $request){
        $query = SaleOrder::with(['customer', 'user']);
        
        $query->where('sister_concern_id', 1);
        if ($request->input('start_date') && $request->input('end_date')) {
            $query->whereBetween('date', [Carbon::parse($request->input('start_date'))->format('Y-m-d'), Carbon::parse($request->input('end_date'))->format('Y-m-d')]);
        }
        
        $query->where('type', SaleType::$ECOMMERCE);
        $query->orderBy('id', 'desc');

        if(request('type') === 'trash'){
            $query->onlyTrashed();
        }else{
            if(request('type') === 'cancel'){
                $query->where('status', 4);
            }else{
                $query->whereIn('status', [0, 1, 2, 3]);
            }
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('customer_name', function (SaleOrder $order) {
                return $order->customer->name ?? '';
            })
            ->addColumn('user', function (SaleOrder $order) {
                return $order->user->name ?? '';
            })
            ->addColumn('customer_mobile_no', function (SaleOrder $order) {
                return $order->customer->mobile_no ?? '';
            })
            ->addColumn('status', function (SaleOrder $order) {
                if ($order->status == 0) return '<span class="badge badge-warning">Pending</span>';
                elseif ($order->status == 1) return '<span class="badge badge-info">Processing</span>';
                elseif ($order->status == 2) return '<span class="badge badge-info">In-transit</span>';
                elseif ($order->status == 3) return '<span class="badge badge-success">Completed</span>';
                elseif ($order->status == 4) return '<span class="badge badge-danger">Cancelled</span>';
            })
            ->addColumn('action', function (SaleOrder $order) {
                $btn = '';
                $btn .= '<a href="' . route('ecom_order.details', ['order' => $order->id]) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a> ';
                if($order->deleted_at == null && $order->status != 4){
                    if($order->status == 0 && auth()->user()->can('ecom_order_confirm')){
                        $btn .= '<a class="btn btn-info btn-sm" href="' . route('ecom_order.confirm', ['order' => $order->id]) . '"><i class="fas fa-shipping-fast"></i></a> ';
                    }
                    if ($order->status > 0 && auth()->user()->can('ecom_order_status')) {
                        $btn .= '<a role="button" class="btn btn-info btn-sm btn-status" data-id="' . $order->id . '" data-status="' . $order->status . '">Order Status</a> ';
                    }
                    if ($order->status == 0 && auth()->user()->can('ecom_order_status')) {
                        $btn .= '<a role="button" class="btn btn-danger btn-sm btn-cancel-status" data-id="' . $order->id . '" data-status="' . $order->status . '">Cancel</a> ';
                    }
                    if (auth()->user()->can('ecom_order_delete')) {
                        $btn .= ' <a role="button" class="btn btn-danger btn-sm btn-delete" data-id="' . $order->id . '"><i class="fa fa-trash"></i></a> ';
                    }
                }
                return $btn;
            })
            ->editColumn('date', function (SaleOrder $order) {
                return Carbon::parse($order->date)->format('d-M-Y');
            })
            ->editColumn('total', function (SaleOrder $order) {
                return 'à§³' . number_format($order->total, 2);
            })
            ->editColumn('paid', function (SaleOrder $order) {
                return 'à§³' . number_format($order->paid, 2);
            })
            ->editColumn('due', function (SaleOrder $order) {
                return 'à§³' . number_format($order->due, 2);
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
    }
    
    public function salesConfirm(SaleOrder $order){
        if ($checkout = $order->packageCheckout) return redirect()->route('package-orders.show', $checkout);
        if($order->status > 0){
            return redirect()->route('ecom_order')->with('error','Order Already Confirmed');
        } 
        $customers = Supplier::orderBy('name')->get();
        $accounts = AccountHead::where('payment_mode', 1)->where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 1)->orderBy('name')->get();
        $mobileBankings = AccountHead::where('payment_mode', 3)->where('status', 1)->orderBy('name')->get();

        return view('ecommerce.order.edit', compact(
            'order',
            'accounts',
            'customers',
            'products',
            'mobileBankings'
        ));
    }

    public function salesConfirmPost(SaleOrder $order, Request $request){
        if ($checkout = $order->packageCheckout) return redirect()->route('package-orders.show', $checkout);
        $total = $request->total;
        $rules = [
            'sister_concern_id' => 'required',
            'customer' => 'required',
            'product.*' => 'required',
            'batch.*' => 'required',
            'quantity.*' => 'required|numeric|min:.01',
            'unit_price.*' => 'required|numeric|min:0',
            'date' => 'required|date',
            'received_by' => 'nullable|string|max:255',
            'discount.*' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0|max:' . $total,
            'paid_two' => 'required|numeric|min:0|max:' . $total,
            'paid_three' => 'required|numeric|min:0|max:' . $total,
            'payment_type' => 'nullable',
            'type' => 'required',
        ];

        if($request->paid > 0){
            $rules['payment_type'] = 'required';
            if ($request->payment_type == '2') $rules['account'] = 'required';
            if ($request->payment_type == '3') $rules['mobile_banking'] = 'required';
        }
        if ($request->paid_two > 0) {
            $rules['payment_type_two'] = 'required';
            if ($request->payment_type_two == '2') $rules['account_two'] = 'required';
            if ($request->payment_type_two == '3') $rules['mobile_banking_two'] = 'required';
        }
        if ($request->paid_three > 0) {
            $rules['payment_type_three'] = 'required';
            if ($request->payment_type_three == '2') $rules['account_three'] = 'required';
            if ($request->payment_type_three == '3') $rules['mobile_banking_three'] = 'required';
        }

        $request->validate($rules);

        if ($request->paid_three + $request->paid_two + $request->paid > $total) {
            return redirect()->back()->withInput()->with('error', 'Paid amount is greater than Order amount!');
        }

        $available = true;
        $message = '';

        if ($request->product) {
            foreach ($request->product as $key => $productId) {
                $batch_no = str_replace("Batch-", "", $request->batch[$key]);
                $inventory = Inventory::where('product_id', $productId)
                    ->where('batch_no', $batch_no)
                    ->where('sister_concern_id', $request->sister_concern_id)
                    ->first();
                
                if (!$inventory || $request->quantity[$key] > $inventory->quantity) {
                    $available = false;
                    $message = 'Insufficient stock for ' . (Product::find($productId)->name ?? 'Item');
                    break;
                }
            }
        }

        if (!$available) {
            return redirect()->back()->withInput()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            // Reverse previous stock
            foreach ($order->saleOrderItems as $item) {
                Inventory::where('id', $item->inventory_id)->increment('quantity', $item->quantity);
            }
            SaleOrderItem::where('sale_order_id', $order->id)->delete();
            InventoryLog::where('sale_order_id', $order->id)->delete();
            
            // Reverse previous accounting
            Voucher::where('sale_order_id', $order->id)->delete();
            Transaction::where('sale_order_id', $order->id)->delete();
            SalePayment::where('sale_order_id', $order->id)->delete();

            $totalDiscount = array_sum($request->discount_amt ?? []);
            
            $order->sister_concern_id = $request->sister_concern_id;
            $order->customer_id = $request->customer;
            $order->type = $request->type;
            $order->received_by = $request->received_by;
            $order->date = Carbon::parse($request->date);
            $order->discount = $totalDiscount + $request->coupon_discount;
            $order->coupon_discount = $request->coupon_discount;
            $order->shipping_charge = $request->shipping_charge;
            $order->paid = $request->paid + $request->paid_two + $request->paid_three;
            $order->note = $request->notes;
            $order->user_id = Auth::id();
            $order->status = 1; // Processing
            
            $subTotal = 0;
            $costingTotal = 0;

            if ($request->product) {
                foreach ($request->product as $key => $productId) {
                    $batch_no = str_replace("Batch-", "", $request->batch[$key]);
                    $inventory = Inventory::where('product_id', $productId)
                        ->where('sister_concern_id', $request->sister_concern_id)
                        ->where('batch_no', $batch_no)
                        ->first();

                    $saleItem = new SaleOrderItem();
                    $saleItem->sister_concern_id = $request->sister_concern_id;
                    $saleItem->sale_order_id = $order->id;
                    $saleItem->inventory_id = $inventory->id;
                    $saleItem->product_id = $productId;
                    $saleItem->product_code = $inventory->product_code;
                    $saleItem->quantity = $request->quantity[$key];
                    $saleItem->unit_price = $inventory->unit_price;
                    $saleItem->selling_price = $request->unit_price[$key];
                    $saleItem->discount_percent = $request->discount[$key];
                    $saleItem->discount_amount = $request->discount_amt[$key];
                    $saleItem->date = Carbon::parse($request->date);
                    $saleItem->save();

                    $costingTotal += $inventory->unit_price * $request->quantity[$key];
                    $subTotal += $request->quantity[$key] * $request->unit_price[$key];
                    
                    $inventory->decrement('quantity', $request->quantity[$key]);
                    
                    // Inventory Log
                    $this->inventoryLog($order, $inventory, $request->quantity[$key], $request->unit_price[$key], $inventory->product_code);
                }
            }

            $order->sub_total = $subTotal;
            $order->total = $subTotal - $totalDiscount - $request->coupon_discount + $request->shipping_charge;
            $order->due = $order->total - $order->paid;
            $order->save();

        // Accounting Entries
        $this->createAccountingEntries($order, $costingTotal, $request);

        DB::commit();
        return redirect()->route('ecom_order.details', ['order' => $order->id])->with('success', 'Order processed successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
}

private function getPayeeId($saleOrder)
{
    $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
    $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();
    if (!$customerAccountHead) {
        $customerAccountHead = new AccountHead();
        $customerAccountHead->code = generateAccountCode($receivablesGroup->top_reserved_id ?? null);
        $customerAccountHead->sister_concern_id = null;
        $customerAccountHead->account_group_id = $receivablesGroup ? $receivablesGroup->id : 9; // Accounts Receivable
        $customerAccountHead->supplier_id = $saleOrder->customer_id;
        $customerAccountHead->name = ($saleOrder->customer->name ?? 'Customer') . '(Customer)';
        $customerAccountHead->save();
    }
    return $customerAccountHead->id;
}

private function createAccountingEntries($saleOrder, $costingTotal, $request)
{
    $payeeId = $this->getPayeeId($saleOrder);

        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
        $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = new Voucher();
        $voucher->sister_concern_id = $saleOrder->sister_concern_id;
        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $voucher->amount = $saleOrder->total + $saleOrder->discount + $costingTotal;
        $voucher->date = $saleOrder->date->format('Y-m-d');
        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
        $voucher->voucher_no = $voucherNo;
        $voucher->account_head_payee_depositor_id = $payeeId;
        $voucher->sale_order_id = $saleOrder->id;
        $voucher->user_id = Auth::id();
        $voucher->save();

        $this->transaction($voucher, $payeeId, TransactionType::$DEBIT, $saleOrder->total, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id);

        if ($saleOrder->discount > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_DISCOUNT), TransactionType::$DEBIT, $saleOrder->discount, $saleOrder->id);
        }

        if ($costingTotal > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), TransactionType::$DEBIT, $costingTotal, $saleOrder->id);
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), TransactionType::$CREDIT, $costingTotal, $saleOrder->id);
        }

        // Payments
        $payments = [
            ['amount' => $request->paid, 'type' => $request->payment_type, 'account' => $request->account, 'mobile' => $request->mobile_banking],
            ['amount' => $request->paid_two, 'type' => $request->payment_type_two, 'account' => $request->account_two, 'mobile' => $request->mobile_banking_two],
            ['amount' => $request->paid_three, 'type' => $request->payment_type_three, 'account' => $request->account_three, 'mobile' => $request->mobile_banking_three],
        ];

        foreach ($payments as $p) {
            if ($p['amount'] > 0) {
                $paymentMethodHeadId = null;
                if ($p['type'] == 1) { // Cash
                    $paymentMethodHeadId = reservedAccountHeadId(ReservedAccountHead::CASH_IN_HAND);
                } elseif ($p['type'] == 2) { // Bank
                    $paymentMethodHeadId = $p['account'];
                } elseif ($p['type'] == 3) { // Mobile
                    $paymentMethodHeadId = $p['mobile'];
                }

                if ($paymentMethodHeadId) {
                    $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                    $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
                    $pVoucher = new Voucher();
                    $pVoucher->sister_concern_id = $saleOrder->sister_concern_id;
                    $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                    $pVoucher->payment_type_id = AccountHead::find($paymentMethodHeadId)->payment_mode ?? null;
                    $pVoucher->payment_account_head_id = $paymentMethodHeadId; // Receipt Mode
                    $pVoucher->amount = $p['amount'];
                    $pVoucher->date = $saleOrder->date->format('Y-m-d');
                    $pVoucher->voucher_no_group_sl = $pNoGroupSl;
                    $pVoucher->voucher_no = $pNo;
                    $pVoucher->account_head_payee_depositor_id = $payeeId;
                    $pVoucher->sale_order_id = $saleOrder->id;
                    $pVoucher->user_id = Auth::id();
                    $pVoucher->save();

                    $this->transaction($pVoucher, $paymentMethodHeadId, TransactionType::$DEBIT, $p['amount'], $saleOrder->id);
                    $this->transaction($pVoucher, $payeeId, TransactionType::$CREDIT, $p['amount'], $saleOrder->id);

                    $payment = new SalePayment();
                    $payment->sister_concern_id = $saleOrder->sister_concern_id;
                    $payment->sale_order_id = $saleOrder->id;
                    $payment->customer_id = $saleOrder->customer_id;
                    $payment->voucher_id = $pVoucher->id;
                    $payment->amount = $p['amount'];
                    $payment->date = $saleOrder->date->format('Y-m-d');
                    $payment->type = 1;
                    $payment->user_id = Auth::id();
                    $payment->save();
                }
            }
        }
    }

    private function transaction($voucher, $headId, $type, $amount, $saleOrderId)
    {
        $transaction = new Transaction();
        $transaction->sister_concern_id = $voucher->sister_concern_id;
        $transaction->voucher_id = $voucher->id;
        $transaction->account_head_id = $headId;
        $transaction->voucher_type = $voucher->voucher_type;
        $transaction->transaction_type = $type;
        $transaction->amount = $amount;
        $transaction->date = $voucher->date;
        $transaction->voucher_no_group_sl = $voucher->voucher_no_group_sl;
        $transaction->voucher_no = $voucher->voucher_no;
        $transaction->sale_order_id = $saleOrderId;
        $transaction->user_id = Auth::id();
        $transaction->save();
    }

    private function inventoryLog($saleOrder, $inventory, $qty, $sellingPrice, $code)
    {
        $log = new InventoryLog();
        $log->type = 2; // Sale
        $log->sister_concern_id = $saleOrder->sister_concern_id;
        $log->inventory_id = $inventory->id;
        $log->supplier_id = $saleOrder->customer_id;
        $log->sale_order_id = $saleOrder->id;
        $log->product_id = $inventory->product_id;
        $log->batch_no = $inventory->batch_no;
        $log->quantity = $qty;
        $log->unit_price = $inventory->unit_price;
        $log->selling_price = $sellingPrice;
        $log->product_code = $code;
        $log->user_id = Auth::id();
        $log->date = $saleOrder->date;
        $log->save();
    }

    public function details(SaleOrder $order){
        if ($checkout = $order->packageCheckout) return redirect()->route('package-orders.show', $checkout);
        $accounts = AccountHead::where('payment_mode', 1)->where('status', 1)->orderBy('name')->get();
        $mobileBankings = AccountHead::where('payment_mode', 3)->where('status', 1)->orderBy('name')->get();
        return view('ecommerce.order.details', compact('order', 'accounts', 'mobileBankings'));
    }

    public function orderReceiptPrint($id){
        $order = SaleOrder::where('id',$id)->first();
        $accounts = AccountHead::where('payment_mode', 1)->where('status', 1)->orderBy('name')->get();
        $mobileBankings = AccountHead::where('payment_mode', 3)->where('status', 1)->orderBy('name')->get();
        return view('ecommerce.order.print', compact('order', 'accounts', 'mobileBankings'));
    }

    public function orderStatus(Request $request)
    {
        abort_if(!auth()->user()->can('ecommerce_order_status'), 403);
        DB::beginTransaction();
        try {
            $order = SaleOrder::find($request->status_order);
            
            if($request->status == '4'){
                if($order->status > 0){
                    return response()->json(['success' => false, 'message' => "You can't cancel this Order! Only pending orders can be cancelled."]);
                }
            }
            if($order->status > $request->status){
                return response()->json(['success' => false, 'message' => "You can't move back to a previous status!"]);
            }
            if($order->status == $request->status){
                return response()->json(['success' => false, 'message' => "Order is already in this status!"]);
            }
            
            if ($order->packageCheckout && ($order->packageCheckout->status !== 'Confirmed' || !in_array((int)$request->status, [2,3], true))) { DB::rollBack(); return response()->json(['success'=>false,'message'=>'Use the Package Orders workflow.']); }
            $order->status = $request->status;
            if($request->status == '4') $order->note = $request->note;
            $order->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order status updated successfully']);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function getOrderDetails(Request $request)
    {
        $order = SaleOrder::where('id', $request->orderId)
            ->with('customer')
            ->first();

        return response()->json($order);
    }

    public function makePayment(Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'date' => 'required|date',
        ];

        if ($request->payment_type) {
            $rules['amount'] = 'required|numeric|min:0';
            if ($request->payment_type == '2') $rules['account'] = 'required';
            if ($request->payment_type == '3') $rules['mobile_banking'] = 'required';
        }
        if ($request->payment_type_two) {
            $rules['amount_two'] = 'required|numeric|min:0';
            if ($request->payment_type_two == '2') $rules['account_two'] = 'required';
            if ($request->payment_type_two == '3') $rules['mobile_banking_two'] = 'required';
        }
        if ($request->payment_type_three) {
            $rules['amount_three'] = 'required|numeric|min:0';
            if ($request->payment_type_three == '2') $rules['account_three'] = 'required';
            if ($request->payment_type_three == '3') $rules['mobile_banking_three'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $order = SaleOrder::find($request->order_id);
        $totalPayment = (float)($request->amount ?? 0) + (float)($request->amount_two ?? 0) + (float)($request->amount_three ?? 0);
        
        if ($totalPayment > $order->due) {
            return response()->json(['success' => false, 'message' => 'Total payment amount is greater than due amount!']);
        }
        if ($totalPayment <= 0) {
            return response()->json(['success' => false, 'message' => 'Payment amount must be greater than 0!']);
        }

        DB::beginTransaction();
        try {
            $payeeId = $this->getPayeeId($order);
            
            $payments = [];
            if ($request->payment_type && $request->amount > 0) {
                $head = null;
                if ($request->payment_type == 1) $head = reservedAccountHeadId(ReservedAccountHead::CASH_IN_HAND);
                elseif ($request->payment_type == 2) $head = $request->account;
                elseif ($request->payment_type == 3) $head = $request->mobile_banking;
                $payments[] = ['head' => $head, 'amount' => $request->amount];
            }
            if ($request->payment_type_two && $request->amount_two > 0) {
                $head = null;
                if ($request->payment_type_two == 1) $head = reservedAccountHeadId(ReservedAccountHead::CASH_IN_HAND);
                elseif ($request->payment_type_two == 2) $head = $request->account_two;
                elseif ($request->payment_type_two == 3) $head = $request->mobile_banking_two;
                $payments[] = ['head' => $head, 'amount' => $request->amount_two];
            }
            if ($request->payment_type_three && $request->amount_three > 0) {
                $head = null;
                if ($request->payment_type_three == 1) $head = reservedAccountHeadId(ReservedAccountHead::CASH_IN_HAND);
                elseif ($request->payment_type_three == 2) $head = $request->account_three;
                elseif ($request->payment_type_three == 3) $head = $request->mobile_banking_three;
                $payments[] = ['head' => $head, 'amount' => $request->amount_three];
            }

            foreach ($payments as $p) {
                $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
                $pVoucher = new Voucher();
                $pVoucher->sister_concern_id = $order->sister_concern_id;
                $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $pVoucher->payment_type_id = \App\Models\Account\AccountHead::find($p['head'])->payment_mode ?? null;
                $pVoucher->payment_account_head_id = $p['head']; // Receipt Mode
                $pVoucher->amount = $p['amount'];
                $pVoucher->date = Carbon::parse($request->date)->format('Y-m-d');
                $pVoucher->voucher_no_group_sl = $pNoGroupSl;
                $pVoucher->voucher_no = $pNo;
                $pVoucher->account_head_payee_depositor_id = $payeeId;
                $pVoucher->sale_order_id = $order->id;
                $pVoucher->user_id = Auth::id();
                $pVoucher->save();

                $this->transaction($pVoucher, $p['head'], TransactionType::$DEBIT, $p['amount'], $order->id);
                $this->transaction($pVoucher, $payeeId, TransactionType::$CREDIT, $p['amount'], $order->id);

                $payment = new SalePayment();
                $payment->sister_concern_id = $order->sister_concern_id;
                $payment->sale_order_id = $order->id;
                $payment->customer_id = $order->customer_id;
                $payment->voucher_id = $pVoucher->id;
                $payment->amount = $p['amount'];
                $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                $payment->type = 2; // Due Payment
                $payment->user_id = Auth::id();
                $payment->save();
            }

            $order->increment('paid', $totalPayment);
            $order->decrement('due', $totalPayment);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment received successfully.', 'redirect_url' => route('ecom_order.details', ['order' => $order->id])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function orderDelete(Request $request)
    {
        $order = SaleOrder::find($request->orderId);
        if ($order) {
            abort_if($order->packageCheckout !== null, 403, 'Use the package order workflow; package accounting cannot be deleted here.');
            $order->delete();
            return response()->json(['success' => true, 'message' => 'Order deleted successfully.']);
        }
        return response()->json(['success' => false, 'message' => 'Order not found.']);
    }

    public function instantOrder(Request $request)
    {
        abort_if(Product::where('catalog_type', 'package')->whereIn('id', collect($request->input('items', []))->pluck('product_id'))->exists(), 422, 'Use the package checkout workflow.');
        $rules = [
            'recaptchaToken' => 'required',
            'billing.name' => 'required|string|max:255',
            'billing.email' => 'nullable',
            'billing.phone' => 'required|numeric|regex:/^01[3-9]\d{8}$/',
            'shipping.country' => 'required|string',
            'shipping.address' => 'required|string',
            'shipping.city' => 'required|string',
            'shipping.zip' => 'required|string',
        ];
        
        $request->validate($rules, [
            'billing.phone.regex' => 'The mobile number must start with 01 and be 11 digits long.',
            'recaptchaToken.required' => 'Please complete the reCAPTCHA.',
        ]);

        DB::beginTransaction();
        try {
            $request['sister_concern_id'] = 1;
            $checkCustomer = Supplier::where('mobile_no', $request->billing['phone'])->first();
            
            if($checkCustomer){
                $userId = User::where('customer_id', $checkCustomer->id)->first()->id ?? null;
                if(!$userId){
                    $user = new User();
                    $user->name = $checkCustomer->name;
                    $user->username = $checkCustomer->mobile_no;
                    $user->email = $checkCustomer->email ?? $checkCustomer->mobile_no.'@gmail.com';
                    $user->customer_id = $checkCustomer->id;
                    $user->password = bcrypt($checkCustomer->mobile_no);
                    $user->role_id = 3;
                    $user->save();
                    $userId = $user->id;
                }
                $customerId = $checkCustomer->id;
            } else {
                $customer = new Supplier();
                $customer->name = $request->billing['name'];
                $customer->sister_concern_id = 1;
                $customer->email = $request->billing['email'];
                $customer->mobile_no = $request->billing['phone'];
                $customer->address = $request->shipping['address'];
                $customer->save();

                $user = new User();
                $user->name = $customer->name;
                $user->username = $customer->mobile_no;
                $user->email = $customer->email ?? $customer->mobile_no.'@gmail.com';
                $user->customer_id = $customer->id;
                $user->password = bcrypt($customer->mobile_no);
                $user->role_id = 3;
                $user->save();

                $userId = $user->id;
                $customerId = $customer->id;
            }

            $lastSaleOrder = SaleOrder::where('sister_concern_id', $request->sister_concern_id)->where('type', SaleType::$ECOMMERCE)->latest('id')->first();
            $nextCodeNumber = !empty($lastSaleOrder) ? intval(substr($lastSaleOrder->order_no, 7)) + 1 : 1;
            $newCodeOrder = 'ECOMSO-' . str_pad($nextCodeNumber, 10, '0', STR_PAD_LEFT);
            
            $order = new SaleOrder();
            $order->order_no = $newCodeOrder;
            $order->sister_concern_id = $request->sister_concern_id;
            $order->customer_id = $customerId;
            $order->type = SaleType::$ECOMMERCE;
            $order->date = date('Y-m-d');
            $order->sub_total = 0;
            $order->discount = 0;
            $order->shipping_charge = $request->shipping_charge;
            $order->total = 0;
            $order->paid = 0;
            $order->due = 0;
            $order->user_id = $userId;
            $order->status = 0; // Initial
            $order->save();

            EcomOrderInfo::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'order_number' => $newCodeOrder,
                'pi_name' => $request->billing['name'],
                'pi_email' => $request->billing['email'],
                'pi_phone_no' => $request->billing['phone'],
                'country' => $request->shipping['country'],
                'shipping_city' => $request->shipping['city'],
                'zip_code' => $request->shipping['zip'],
                'order_notes' => $request->shipping['orderNotes'] ?? '',
                'total_amount' => $request->totals['total'],
                'discount' => $request->totals['discount'],
                'status' => 'pending',
                'shipping_address' => $request->shipping['address'],
                'billing_address' => $request->billing['address'] ?? null,
                'payment_method' => $request->paymentMethod,
                'payable_amount' => $request->totals['total'],
                'ordered_at' => now(),
            ]);

            $subTotal = 0;
            $totalDiscount = 0;
            $items = $request->input('items');
            if ($items) {
                foreach ($items as $item) {
                    $product = Product::find($item['product_id']);
                    $inventory = Inventory::where('product_id', $product->id)
                        ->where('sister_concern_id', $request->sister_concern_id)
                        ->where('batch_no', $item['batch_no'])
                        ->first();

                    $saleItem = new SaleOrderItem();
                    $saleItem->sister_concern_id = $request->sister_concern_id;
                    $saleItem->product_id = $product->id;
                    $saleItem->sale_order_id = $order->id;
                    $saleItem->inventory_id = $inventory->id ?? null;
                    $saleItem->product_code = $product->code;
                    $saleItem->quantity = $item['quantity'];
                    $saleItem->unit_price = $inventory->unit_price ?? 0;
                    $saleItem->selling_price = $item['price'] + $item['discount'];
                    $saleItem->discount_percent = $item['discount_percent'];
                    $saleItem->discount_amount = $item['discount'];
                    $saleItem->date = date('Y-m-d');
                    $saleItem->save();

                    $subTotal += ($item['price'] + $item['discount']) * $item['quantity'];
                    $totalDiscount += $item['discount'] * $item['quantity'];
                }
            }

            $order->sub_total = $subTotal;
            $order->discount = $totalDiscount;
            $order->total = ceil($subTotal - $totalDiscount + $request->shipping_charge);
            $order->due = $order->total;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
                'order_number' => $order->order_no,
                'redirect' => route('bkash-create', ['order_id' => $order->id, 'type' => 'Ecom']) 
            ]);
        } catch (\Exception $e) { 
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

