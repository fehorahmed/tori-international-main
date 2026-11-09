<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Enumeration\PaymentPurpose;
use App\Models\Division;
use App\Models\Supplier;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SalePayment;
use App\Models\Account\AccountHead;
use App\Enumeration\SupplierType;
use App\Enumeration\SaleType;
use App\Enumeration\SalePaymentType;
use App\Models\ReturnsManagement\SalesReturn;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $customer = null;
        if ($request->customer_id) {
            $customer = Supplier::find($request->customer_id);
        }
        $divisions = Division::orderBy('name')->get();
        return view('sales.customer_payment.index', compact('customer', 'divisions'));
    }

    public function history(Request $request)
    {
        $customer = Supplier::findOrFail($request->customer_id);
        return view('sales.customer_payment.history', compact('customer'));
    }

    public function historyDatatable(Request $request)
    {
        $query = Transaction::with(['accountHead.supplier', 'voucher.saleOrder', 'voucher.salesReturn'])
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('transaction_type', TransactionType::$CREDIT)
            ->whereIn('voucher_type', [VoucherType::$COLLECTION_VOUCHER, VoucherType::$JOURNAL_VOUCHER])
            ->whereHas('accountHead', function($q) {
                $q->whereNotNull('supplier_id');
            });

        if ($request->customer) {
            $query->whereHas('accountHead', function($q) use ($request) {
                $q->where('supplier_id', $request->customer);
            });
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [
                \Carbon\Carbon::parse($request->start_date)->format('Y-m-d'), 
                \Carbon\Carbon::parse($request->end_date)->format('Y-m-d'), 
            ]);
        }

        $query->orderBy('id', 'desc');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('date', function (Transaction $transaction) {
                return $transaction->date ? Carbon::parse($transaction->date)->format('d-m-Y') : '';
            })
            ->addColumn('customer_name', function (Transaction $transaction) {
                return $transaction->accountHead->supplier->name ?? '';
            })
            ->addColumn('order_no', function (Transaction $transaction) {
                if ($transaction->voucher && $transaction->voucher->salesReturn) {
                    $salesReturn = $transaction->voucher->salesReturn;
                    $route = route('returns-management.sales-returns.show', $salesReturn->id);
                    return '<a href="'.$route.'" class="text-info font-weight-bold">'.$salesReturn->return_no.'</a>';
                }

                $order = null;
                if ($transaction->voucher && $transaction->voucher->saleOrder) {
                    $order = $transaction->voucher->saleOrder;
                } elseif ($transaction->saleOrder) {
                    $order = $transaction->saleOrder;
                }

                if ($order) {
                    $route = route('sales.details', ['sale' => $order->id]);
                    if ($order->type == SaleType::$CORPORATE) {
                        $route = route('corporate-sales.show', ['corporate_sale' => $order->id]);
                    } elseif ($order->type == SaleType::$DEALER) {
                        $route = route('dealer-sales.show', ['dealer_sale' => $order->id]);
                    }
                    return '<a href="'.$route.'" class="text-info font-weight-bold">'.$order->order_no.'</a>';
                }

                return '-';
            })
            ->addColumn('voucher_no', function (Transaction $transaction) {
                if ($transaction->voucher) {
                    $printRoute = route('voucher.print', ['voucher' => $transaction->voucher->id]);
                    return '<a href="'.$printRoute.'" target="_blank" class="btn btn-xs btn-info bg-gradient-info text-white" title="Print"><i class="fa fa-print"></i></a>';
                }
                return '-';
            })
            ->addColumn('amount', function (Transaction $transaction) {
                return number_format($transaction->amount, 2);
            })
            ->rawColumns(['voucher_no', 'order_no'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $supplier = Supplier::findOrFail($request->customer_id);
        
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $supplier->id)
            ->first();
            
        $opening_balance = $supplier->opening_balance ?? 0;
        
        // Find how much of the opening balance is already paid
        $paid_opening_due = Transaction::where('account_head_id', $accountHead->id ?? 0)
            ->where('transaction_type', TransactionType::$CREDIT)
            ->where('notes', 'Opening Due Payment')
            ->sum('amount');
            
        $remaining_opening_due = max(0, $opening_balance - $paid_opening_due);
        
        // Find unpaid or partially paid sale orders
        $unpaidOrders = SaleOrder::where('customer_id', $supplier->id)
            ->whereNotIn('status', [1, 4])
            ->where('type', '!=', SaleType::$PRE_ORDER)
            ->whereColumn('total', '>', 'paid')
            ->get();
            
        // Calculate available advance
        $balance = $supplier->customer_current_balance;
        $available_advance = $balance < 0 ? abs($balance) : 0;
        
        // Fetch payment modes (Account Heads)
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })
            ->where('payment_mode', '>', 0)
            ->where('status', 1)
            ->get();

        return view('sales.customer_payment._payment_form', compact(
            'supplier', 'remaining_opening_due', 'unpaidOrders', 'available_advance', 'paymentModes'
        ));
    }
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('sales_payment_receive')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'rm_no' => 'required|string',
            'payment_mode' => 'required',
            'total_receive_amount' => 'nullable|numeric|min:0',
        ]);

        $customer = Supplier::findOrFail($request->customer_id);
        $totalReceive = $request->total_receive_amount ? (float) $request->total_receive_amount : 0;
        $useAdvance = $request->use_advance ? true : false;
        
        if ($totalReceive <= 0 && !$useAdvance) {
            return redirect()->back()->with('error', 'Please enter a valid receive amount.');
        }

        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        // Calculate Total Due
        $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $customer->id)->first();
        if(!$accountHead) {
            return redirect()->back()->with('error', 'Customer Account Head not found!');
        }

        $opening_balance = $customer->opening_balance ?? 0;
        $paid_opening_due = Transaction::where('account_head_id', $accountHead->id)->where('transaction_type', TransactionType::$CREDIT)->where('notes', 'Opening Due Payment')->sum('amount');
        $remaining_opening_due = max(0, $opening_balance - $paid_opening_due);
        
        $totalDue = $remaining_opening_due;
        $unpaidOrders = SaleOrder::where('customer_id', $customer->id)->whereNotIn('status', [1, 4])->where('type', '!=', SaleType::$PRE_ORDER)->whereColumn('total', '>', 'paid')->get();
        foreach($unpaidOrders as $order) {
            $totalDue += ($order->total - $order->paid);
        }

        // Calculate Total Allocated
        $allocations = [];
        $totalAllocated = 0;

        if ($request->allocate_opening_due && $request->allocate_opening_due > 0) {
            $amt = (float) $request->allocate_opening_due;
            $totalAllocated += $amt;
            $allocations[] = ['type' => 'opening_due', 'amount' => $amt, 'id' => null];
        }
        
        if ($request->orders && is_array($request->orders)) {
            foreach ($request->orders as $orderData) {
                if (!empty($orderData['amount']) && $orderData['amount'] > 0) {
                    $amt = (float) $orderData['amount'];
                    $totalAllocated += $amt;
                    $allocations[] = ['type' => 'order', 'amount' => $amt, 'id' => $orderData['id']];
                }
            }
        }

        // Available Advance Use Check
        $useAdvance = $request->has('use_advance') && $request->use_advance == 1;
        $available_advance = $customer->customer_current_balance < 0 ? abs($customer->customer_current_balance) : 0;

        $totalAvailable = $totalReceive + ($useAdvance ? $available_advance : 0);
        $unallocated = $totalAvailable - $totalAllocated;

        if ($unallocated > 0.01 && $totalAllocated < $totalDue - 0.01) {
            return redirect()->back()->with('error', 'You cannot keep Advance without clearing all pending Dues first!');
        }

        DB::beginTransaction();

        try {
            $payment_mode_data = explode('|', $request->payment_mode);
            $payment_account_head_id = $payment_mode_data[0];
            $payment_mode_type = $payment_mode_data[1];

            $remainingCash = $totalReceive;
            $remainingAdvance = $useAdvance ? $available_advance : 0;

            foreach ($allocations as $allocation) {
                $amtToAllocate = $allocation['amount'];

                // 1. Pay via Cash first
                if ($remainingCash > 0 && $amtToAllocate > 0) {
                    $cashAmt = min($remainingCash, $amtToAllocate);
                    
                    $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                    $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                    $voucher = new Voucher();
                    $voucher->sister_concern_id = auth()->user()->activeConcernId();
                    $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                    $voucher->amount = $cashAmt;
                    $voucher->date = $date;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->account_head_payee_depositor_id = $accountHead->id;
                    $voucher->payment_type_id = $payment_mode_type;
                    $orderNoString = '';
                    if ($allocation['type'] === 'order') {
                        $order = SaleOrder::find($allocation['id']);
                        if ($order) $orderNoString = ' (' . $order->order_no . ')';
                    }

                    $voucher->payment_account_head_id = $payment_account_head_id;
                    $voucher->cheque_no = $request->cheque_no;
                    $voucher->notes = ($allocation['type'] === 'opening_due' ? 'Opening Due Payment' : 'Order Due Payment' . $orderNoString) . ($request->note ? ' - ' . $request->note : '');
                    $voucher->payment_purpose = $allocation['type'] === 'opening_due' ? PaymentPurpose::$OPENING_DUE_COLLECTION : PaymentPurpose::$ORDER_DUE_COLLECTION;
                    $voucher->sale_order_id = $allocation['id'];
                    $voucher->rm_no = $request->rm_no;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    // Debit: Cash/Bank
                    $debitTx = new Transaction();
                    $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                    $debitTx->voucher_id = $voucher->id;
                    $debitTx->account_head_id = $payment_account_head_id;
                    $debitTx->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                    $debitTx->transaction_type = TransactionType::$DEBIT;
                    $debitTx->amount = $cashAmt;
                    $debitTx->date = $date;
                    $debitTx->voucher_no = $voucherNo;
                    $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                    $debitTx->account_head_payee_depositor_id = $accountHead->id;
                    $debitTx->notes = $voucher->notes;
                    $debitTx->payment_purpose = $voucher->payment_purpose;
                    $debitTx->user_id = auth()->id();
                    $debitTx->save();

                    // Credit: Customer
                    $creditTx = new Transaction();
                    $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                    $creditTx->voucher_id = $voucher->id;
                    $creditTx->account_head_id = $accountHead->id;
                    $creditTx->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                    $creditTx->transaction_type = TransactionType::$CREDIT;
                    $creditTx->amount = $cashAmt;
                    $creditTx->date = $date;
                    $creditTx->voucher_no = $voucherNo;
                    $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                    $creditTx->account_head_payee_depositor_id = $accountHead->id;
                    $creditTx->notes = $voucher->notes;
                    $creditTx->payment_purpose = $voucher->payment_purpose;
                    $creditTx->sale_order_id = $allocation['id'];
                    $creditTx->user_id = auth()->id();
                    $creditTx->save();

                    $remainingCash -= $cashAmt;
                    $amtToAllocate -= $cashAmt;
                }

                // 2. Pay via Advance Offset
                if ($remainingAdvance > 0 && $amtToAllocate > 0) {
                    $advAmt = min($remainingAdvance, $amtToAllocate);
                    
                    $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                    $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                    $voucher = new Voucher();
                    $voucher->sister_concern_id = auth()->user()->activeConcernId();
                    $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $voucher->amount = $advAmt;
                    $voucher->date = $date;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->account_head_payee_depositor_id = $accountHead->id;
                    $voucher->notes = 'Advance Adjusted against Dues' . ($request->note ? ' - ' . $request->note : '');
                    $voucher->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION_ADJUST;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    // Debit: Customer (Offsetting Advance Liability)
                    $debitTx = new Transaction();
                    $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                    $debitTx->voucher_id = $voucher->id;
                    $debitTx->account_head_id = $accountHead->id;
                    $debitTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $debitTx->transaction_type = TransactionType::$DEBIT;
                    $debitTx->amount = $advAmt;
                    $debitTx->date = $date;
                    $debitTx->voucher_no = $voucherNo;
                    $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                    $debitTx->account_head_payee_depositor_id = $accountHead->id;
                    $debitTx->notes = 'Advance Adjusted against Dues' . ($request->note ? ' - ' . $request->note : '');
                    $debitTx->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION_ADJUST;
                    $debitTx->user_id = auth()->id();
                    $debitTx->save();

                    // Credit: Customer (Paying Order/Due)
                    $creditTx = new Transaction();
                    $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                    $creditTx->voucher_id = $voucher->id;
                    $creditTx->account_head_id = $accountHead->id;
                    $creditTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $creditTx->transaction_type = TransactionType::$CREDIT;
                    $creditTx->amount = $advAmt;
                    $creditTx->date = $date;
                    $orderNoString = '';
                    if ($allocation['type'] === 'order') {
                        $order = SaleOrder::find($allocation['id']);
                        if ($order) $orderNoString = ' (' . $order->order_no . ')';
                    }

                    $creditTx->voucher_no = $voucherNo;
                    $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                    $creditTx->account_head_payee_depositor_id = $accountHead->id;
                    $creditTx->notes = ($allocation['type'] === 'opening_due' ? 'Opening Due Payment' : 'Order Due Payment' . $orderNoString) . ($request->note ? ' - ' . $request->note : '');
                    $creditTx->payment_purpose = $allocation['type'] === 'opening_due' ? PaymentPurpose::$OPENING_DUE_COLLECTION : PaymentPurpose::$ORDER_DUE_COLLECTION;
                    $creditTx->sale_order_id = $allocation['id'];
                    $creditTx->user_id = auth()->id();
                    $creditTx->save();

                    $remainingAdvance -= $advAmt;
                    $amtToAllocate -= $advAmt;
                }

                // Update Order Paid Amount
                if ($allocation['type'] === 'order') {
                    $order = SaleOrder::find($allocation['id']);
                    if($order) {
                        $order->increment('paid', $allocation['amount']);
                        $order->decrement('due', $allocation['amount']);
                    }
                }
            }

            // Create Advance Payment record for unallocated cash
            if ($remainingCash > 0) {
                $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $voucher->amount = $remainingCash;
                $voucher->date = $date;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $accountHead->id;
                $voucher->payment_type_id = $payment_mode_type;
                $voucher->payment_account_head_id = $payment_account_head_id;
                $voucher->cheque_no = $request->cheque_no;
                $voucher->notes = 'Advance Receive' . ($request->note ? ' - ' . $request->note : '');
                $voucher->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
                $voucher->rm_no = $request->rm_no;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // Debit: Cash/Bank
                $debitTx = new Transaction();
                $debitTx->sister_concern_id = auth()->user()->activeConcernId();
                $debitTx->voucher_id = $voucher->id;
                $debitTx->account_head_id = $payment_account_head_id;
                $debitTx->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $debitTx->transaction_type = TransactionType::$DEBIT;
                $debitTx->amount = $remainingCash;
                $debitTx->date = $date;
                $debitTx->voucher_no = $voucherNo;
                $debitTx->voucher_no_group_sl = $voucherNoGroupSl;
                $debitTx->account_head_payee_depositor_id = $accountHead->id;
                $debitTx->notes = 'Advance Receive' . ($request->note ? ' - ' . $request->note : '');
                $debitTx->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
                $debitTx->user_id = auth()->id();
                $debitTx->save();

                // Credit: Customer
                $creditTx = new Transaction();
                $creditTx->sister_concern_id = auth()->user()->activeConcernId();
                $creditTx->voucher_id = $voucher->id;
                $creditTx->account_head_id = $accountHead->id;
                $creditTx->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $creditTx->transaction_type = TransactionType::$CREDIT;
                $creditTx->amount = $remainingCash;
                $creditTx->date = $date;
                $creditTx->voucher_no = $voucherNo;
                $creditTx->voucher_no_group_sl = $voucherNoGroupSl;
                $creditTx->account_head_payee_depositor_id = $accountHead->id;
                $creditTx->notes = 'Advance Receive' . ($request->note ? ' - ' . $request->note : '');
                $creditTx->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
                $creditTx->user_id = auth()->id();
                $creditTx->save();
            }

            DB::commit();
            return redirect()->route('customer-payment.index')->with('success', 'Payment processed and allocated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage() . ' Line: ' . $e->getLine());
        }
    }

    public function dataTable(Request $request)
    {
        $type = $request->type ?? SupplierType::$CUSTOMER;

        $query = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', $type);
            
        if ($request->customer_id) {
            $query->where('id', $request->customer_id);
        }

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
            ->addColumn('total_sales', function(Supplier $supplier) {
                return number_format($supplier->purchase_orders_sum_total ?? SaleOrder::where('customer_id', $supplier->id)
                    ->where(function($q) {
                        $q->where('type', '!=', SaleType::$PRE_ORDER)->orWhere('is_delivered', 1);
                    })->sum('total'), 2);
            })
            ->addColumn('total_return', function(Supplier $supplier) {
                // If net_payable_or_receivable is negative, the customer was credited (Due decreases) -> Acts like a positive Net Return
                $netReturn = -1 * SalesReturn::where('customer_id', $supplier->id)->sum('net_payable_or_receivable');
                return number_format($netReturn, 2);
            })
            ->addColumn('total_paid', function(Supplier $supplier) {
                return number_format($supplier->total_customer_paid, 2);
            })
            ->addColumn('current_due_advance', function(Supplier $supplier) {
                $balance = $supplier->customer_current_balance;
                
                if($balance > 0) {
                    return '<span class="badge badge-danger">Due: ' . number_format($balance, 2) . '</span>';
                } elseif($balance < 0) {
                    return '<span class="badge badge-success">Advance: ' . number_format(abs($balance), 2) . '</span>';
                } else {
                    return '<span class="badge badge-secondary">Cleared</span>';
                }
            })
            ->addColumn('action', function(Supplier $supplier) {
                $paymentBtn = '<button class="btn btn-sm btn-info btn-payment mr-1" data-id="'.$supplier->id.'" data-name="'.$supplier->name.'">Receive</button>';
                $historyBtn = '<a href="'.route('customer-payment.history', ['customer_id' => $supplier->id]).'" class="btn btn-sm btn-primary bg-gradient-primary" title="Payment History"><i class="fas fa-history"></i></a>';
                return '<div class="d-flex justify-content-center">'.$paymentBtn . $historyBtn.'</div>';
            })
            ->rawColumns(['opening_balance', 'current_due_advance', 'action'])
            ->make(true);
    }
}
