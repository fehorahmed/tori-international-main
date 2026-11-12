<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;

use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountHead;
use App\Models\Inventory\InventoryLog;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Sales\SaleOrder;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Yajra\DataTables\Facades\DataTables;

class ReceiptPaymentController extends Controller
{

    public function index(Request $request)
    {
        $sister_concerns = SisterConcern::where('status',1)->get();
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $partyLabel = 'Party';
            $permission = 'journal_voucher';
            $permissionCreate = 'journal_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $partyLabel = 'Payee';
            $permission = 'payment_voucher';
            $permissionCreate = 'payment_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $partyLabel = 'Depositor';
            $permission = 'receipt_voucher';
            $permissionCreate = 'receipt_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $partyLabel = 'Depositor';
            $permission = 'contra_voucher';
            $permissionCreate = 'contra_voucher_create';
        } else {
            $partyLabel = '';
            $permissionCreate = '';
            $permission = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }

        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');

        $voucherType = $request->voucher_type;
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            return view('accounts.receipt_payment.index_jv', compact('voucherTitle',
                'voucherType', 'partyLabel', 'permissionCreate','sister_concerns'));
        }
        if ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            return view('accounts.receipt_payment.index_contra_voucher', compact('voucherTitle',
                'voucherType', 'partyLabel', 'permissionCreate','sister_concerns'));
        }
        return view('accounts.receipt_payment.index', compact('voucherTitle',
            'voucherType', 'partyLabel', 'permissionCreate','sister_concerns'));
    }


    public function details(Voucher $voucher)
    {
        $isNotJournalVoucher = true;
        if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $voucherTitleLabel = 'Journal';
            $voucherTitleLabelType = 'Journal';
            $partyLabel = 'Party';
            $isNotJournalVoucher = false;
            $permission = 'journal_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $voucherTitleLabel = 'Payment';
            $voucherTitleLabelType = 'Expenses';
            $partyLabel = 'Payee';
            $permission = 'payment_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $voucherTitleLabel = 'Received';
            $voucherTitleLabelType = 'Received';
            $partyLabel = 'Depositor';
            $permission = 'receipt_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $voucherTitleLabel = 'Contra Voucher';
            $voucherTitleLabelType = 'Received';
            $partyLabel = 'Depositor';
            $permission = 'contra_voucher';
        } else {
            $permission = '';
            $partyLabel = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }

        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
        abort_if($voucher->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        $voucherType = $voucher->voucher_type;
        $voucher->amount_in_word = DecimalToWords::convert($voucher->amount, 'Taka',
            'Poisa');

        return view('accounts.receipt_payment.details', compact('voucher', 'voucherType',
            'voucherTitle', 'partyLabel', 'voucherTitleLabel', 'voucherTitleLabelType', 'isNotJournalVoucher'));
    }

    public function print(Voucher $voucher)
    {
        $isNotJournalVoucher = true;
        if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $voucherTitleLabel = 'Journal';
            $voucherTitleLabelType = 'Journal';
            $partyLabel = 'Party';
            $isNotJournalVoucher = false;
            $permission = 'journal_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $voucherTitleLabel = 'Payment';
            $voucherTitleLabelType = 'Expenses';
            $partyLabel = 'Payee';
            $permission = 'payment_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $voucherTitleLabel = 'Received';
            $voucherTitleLabelType = 'Received';
            $partyLabel = 'Depositor';
            $permission = 'receipt_voucher';
        } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $voucherTitleLabel = 'Contra Voucher';
            $voucherTitleLabelType = 'Received';
            $partyLabel = 'Depositor';
            $permission = 'contra_voucher';
        } else {
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }

        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
        abort_if($voucher->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        $voucherType = $voucher->voucher_type;
        $voucher->amount_in_word = DecimalToWords::convert($voucher->amount, 'Taka',
            'Poisa');

        return view('accounts.receipt_payment.print', compact('voucher', 'voucherType',
            'voucherTitle', 'partyLabel', 'voucherTitleLabel', 'voucherTitleLabelType', 'isNotJournalVoucher'));
    }

    public function dataTable()
    {

        $query = Voucher::where('sister_concern_id',auth()->user()->activeConcernId())->where('voucher_type', request('voucher_type'))
            ->with('paymentAccountHead', 'payeeDepositorAccountHead', 'transactions', 'salesReturn', 'purchaseReturn', 'shomriddhiTransaction', 'employeeAdvance');

        if (\request()->has('purchase_order_id') && \request('purchase_order_id') != ''){
            $query->where('purchase_order_id', request('purchase_order_id'));
        }
        if (request('product_utilize_id') != ''){
            $query->where('product_utilize_id', request('product_utilize_id'));
        }
        if (request('sale_order_id') != ''){
            $query->where('sale_order_id', request('sale_order_id'));
        }
        if (request('flat_sale_order_id') != ''){
            $query->where('flat_sale_order_id', request('flat_sale_order_id'));
        }

        if (request('advance_supplier_id') != ''){
            $query->where('advance_supplier_id', request('advance_supplier_id'));
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if(request('sister_concern') != ''){
            $query->where('sister_concern_id',request('sister_concern'));
        }else{
            if(auth()->user()->role != 'Superadmin'){
                $query->where('sister_concern_id',auth()->user()->activeConcernId());
            }
        }
        $voucherType = request('voucher_type');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Voucher $voucher) use ($voucherType) {
                $btn = '';
                $permissionView = '';
                $permissionEdit = '';
                $permissionDelete = '';
                if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
                    $permissionView = 'journal_voucher';
                    $permissionEdit = 'journal_voucher_edit';
                    $permissionDelete = 'journal_voucher_delete';
                } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                    $permissionView = 'payment_voucher';
                    $permissionEdit = 'payment_voucher_edit';
                    $permissionDelete = 'payment_voucher_delete';
                } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                    $permissionView = 'receipt_voucher';
                    $permissionEdit = 'receipt_voucher_edit';
                    $permissionDelete = 'receipt_voucher_delete';
                } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
                    $permissionView = 'contra_voucher';
                    $permissionEdit = 'contra_voucher_edit';
                    $permissionDelete = 'contra_voucher_delete';
                }
                if (auth()->user()->can($permissionView)) {
                    $btn .= '<a href="' . route('voucher.details', ['voucher' => $voucher->id, 'voucher_type' => $voucherType]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-info-circle"></i></a>';
                }

                if ($voucher->is_locked()) {
                    $btn .= ' <a href="javascript:void(0);" class="btn btn-secondary bg-gradient-secondary btn-sm" title="System Generated Voucher - Locked"><i class="fa fa-lock"></i></a>';
                } else {
                    if (auth()->user()->can($permissionEdit) && $voucher->booking_id == '') {
                        $btn .= ' <a href="' . route('voucher.edit', ['voucher' => $voucher->id, 'voucher_type' => $voucherType]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-edit"></i></a>';
                    }
                    if (auth()->user()->can($permissionDelete) && $voucher->booking_id == '') {
                        $btn .= ' <a role="button" data-id="' . $voucher->id . '" class="btn btn-danger bg-gradient-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }
                
                return dropdownMenuContainer($btn);
            })
            ->editColumn('voucher_type', function (Voucher $voucher) {
                if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER)
                    return 'Journal Voucher';
                elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER)
                    return 'Payment Voucher';
                elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER)
                    return 'Collection Voucher';
                elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER)
                    return 'Contra Voucher';
            })
            ->filterColumn('account_heads', function($query, $keyword) {
                $query->whereHas('transactions', function($q) use ($keyword) {
                    $q->whereHas('accountHead', function($q) use ($keyword) {
                        $q->whereHas('supplier', function($q) use ($keyword) {
                            $q->where('member_no', 'like', "%{$keyword}%");
                        });
                    });
                });
            })
            ->editColumn('date', function (Voucher $voucher) {
                return Carbon::parse($voucher->date)->format('d-m-Y');
            })
            ->addColumn('payment_account_head_name', function (Voucher $voucher) {
                return $voucher->paymentAccountHead->name ?? '';
            })
            ->addColumn('debit_account_heads', function (Voucher $voucher) {
                $accountHeads = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                foreach ($voucher->transactions->where('transaction_type', TransactionType::$DEBIT) as $transaction) {
                    $accountHeads .= '<li>' . ($transaction->accountHead->name ?? '') . '</li>';
                }
                $accountHeads .= '</ul>';
                return $accountHeads;
            })
            ->addColumn('credit_account_heads', function (Voucher $voucher) {
                $accountHeads = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                foreach ($voucher->transactions->where('transaction_type', TransactionType::$CREDIT) as $transaction) {
                    $accountHeads .= '<li>' . ($transaction->accountHead->name ?? '') . '</li>';
                }
                $accountHeads .= '</ul>';
                return $accountHeads;
            })
            ->addColumn('debit_amounts', function (Voucher $voucher) {
                $debit_amounts = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                foreach ($voucher->transactions->where('transaction_type', TransactionType::$DEBIT) as $transaction) {
                    $debit_amounts .= '<li>' . (number_format($transaction->amount,2)) . '</li>';
                }
                $debit_amounts .= '</ul>';
                return $debit_amounts;
            })
            ->addColumn('account_heads', function (Voucher $voucher) {
                $accountHeads = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                foreach ($voucher->transactions->where('account_head_id', '!=', $voucher->payment_account_head_id) as $transaction) {
                    $name = $transaction->accountHead->name ?? '';
                    if (isset($transaction->accountHead->supplier) && $transaction->accountHead->supplier->member_no) {
                        $name .= ' - ' . $transaction->accountHead->supplier->member_no;
                    }
                    $accountHeads .= '<li>' . $name . '</li>';
                }
                $accountHeads .= '</ul>';
                return $accountHeads;
            })
            ->addColumn('amounts', function (Voucher $voucher) {
                $amounts = '<ul style="margin: 0;padding-left: 0;list-style: none">';
                foreach ($voucher->transactions->where('account_head_id', '!=', $voucher->payment_account_head_id) as $transaction) {
                    $amounts .= '<li>' . (number_format($transaction->amount, 2)) . '</li>';
                }
                $amounts .= '</ul>';
                return $amounts;
            })
            ->addColumn('payee_depositor_account_head_name', function (Voucher $voucher) {
                return $voucher->payeeDepositorAccountHead->name ?? '';
            })
            ->rawColumns(['action','debit_amounts','account_heads', 'narrations', 'debit_account_heads', 'credit_account_heads', 'amounts'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->where('status',1)->get();
        $accountHeads = AccountHead::where('payment_mode', 0)->where('is_control_account', 0)->where('status',1)->get();
        $parties = AccountHead::whereNotNull('supplier_id')->where('status',1)->get();
        $voucherType = $request->voucher_type;

        $isNotJournalVoucher = true;
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $partyLabel = 'Party';
            $permission = 'journal_voucher_create';
            $isNotJournalVoucher = false;
        } elseif ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $partyLabel = 'Payee';
            $permission = 'payment_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $partyLabel = 'Depositor';
            $permission = 'receipt_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $accountHeads = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
            $voucherTitle = 'Contra Voucher';
            $partyLabel = '';
            $permission = 'contra_voucher_create';
            return view('accounts.receipt_payment.contra_create', compact('paymentModes',
                'accountHeads', 'parties', 'voucherTitle', 'voucherType', 'partyLabel', 'isNotJournalVoucher'));

        } else {
            $partyLabel = '';
            $permission = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }

        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');

        return view('accounts.receipt_payment.create', compact('paymentModes',
            'accountHeads', 'parties', 'voucherTitle', 'voucherType',
            'partyLabel', 'isNotJournalVoucher'));

    }

    public function store(Request $request)
    {

        if ($request->voucher_type == '')
            abort('404');

        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $rules = [
                'account_head_id.*' => 'required',
                'amount.*' => 'required|numeric|min:.01',
                'payee_or_depositor' => 'nullable',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $rules = [
                'debit_account_head_id.*' => 'required',
                'credit_account_head_id.*' => 'required',
                'debit_amount.*' => 'required|numeric|min:.01',
                'credit_amount.*' => 'required|numeric|min:.01',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        } else {
            $rules = [
                'account_head_id.*' => 'required',
                'amount.*' => 'required|numeric|min:.01',
                'payment_mode' => 'required',
                'payee_or_depositor' => 'nullable',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        }

        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $permission = 'journal_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $permission = 'payment_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $permission = 'receipt_voucher_create';
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $permission = 'contra_voucher_create';
        } else {
            $permission = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');


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

        $debitTotal = 0;
        $creditTotal = 0;
        $total = 0;
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            foreach ($request->account_head_id as $key => $requestAccount) {
                if ($request->trnx_type_val[$key] == TransactionType::$DEBIT) {
                    $debitTotal += $request->amount[$key];
                    $total += $request->amount[$key];
                } else {
                    $creditTotal += $request->amount[$key];
                }
            }
            if ($debitTotal != $creditTotal) {
                return redirect()
                    ->route('voucher.create', ['voucher_type' => $request->voucher_type])
                    ->withInput()->with('error', 'For journal voucher debit and credit amount must be same');
            }
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $debitTotal = array_sum($request->debit_amount);
            $creditTotal = array_sum($request->credit_amount);
            $total = $debitTotal;
            if ($debitTotal != $creditTotal) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'For contra voucher debit and credit amount must be same');
            }
        } else {
            $total = array_sum($request->amount);
        }

        if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $paymentAccount = AccountHead::find($paymentMode);
            // if (!$paymentAccount->isSufficient($total)) {
            //     // throw new \Exception("Insufficient balance in {$paymentAccount->name}. Current balance is " . number_format($paymentAccount->current_balance, 2));
            // }
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            foreach ($request->credit_account_head_id as $key => $headId) {
                $creditAccount = AccountHead::find($headId);
                // if (!$creditAccount->isSufficient($request->credit_amount[$key])) {
                //     // throw new \Exception("Insufficient balance in {$creditAccount->name}. Current balance is " . number_format($creditAccount->current_balance, 2));
                // }
            }
        }

        // Start a database transaction
        DB::beginTransaction();

        try {



            $voucherNo = generateVoucherNo($request->voucher_type);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));


            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            $voucher->voucher_type = $request->voucher_type;
            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $request->payee_or_depositor;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            if ($request->voucher_type != VoucherType::$CONTRA_VOUCHER){
                $otherDeductAmount = 0;
                foreach ($request->account_head_id as $key => $requestAccount) {

                    if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                        //Debit
                        $transactionType = TransactionType::$DEBIT;
                    }elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                        //Credit
                        $transactionType = TransactionType::$CREDIT;
                    }
                    if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER){
                        if ($request->trnx_type_val[$key] == 1){
                            //Debit
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->voucher_id = $voucher->id;
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }else{
                            //Credit
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->voucher_id = $voucher->id;
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }

                    }else{

                        //$request->trnx_type_val = 1 Debit,$request->trnx_type_val[$key] = 2 Credit

                        $other_deduct = 0;
                        if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER){
                            if ($request->trnx_type_val[$key] == 2){
                                $other_deduct = 1;
                            }
                        }else{
                            if ($request->trnx_type_val[$key] == 1){
                                $other_deduct = 1;
                            }
                        }
                        if ($other_deduct == 1){
                            //If any other Deduction
                            $otherDeductAmount +=$request->amount[$key];
                            $lastTransaction = Transaction::where('voucher_id',$voucher->id)
                                ->where('other_deduct',0)
                                ->where('account_head_id','!=',$paymentMode)
                                ->orderBy('id','desc')
                                ->first();
                            if ($lastTransaction){
                                $transaction = new Transaction();
                                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                                $transaction->except_edit = 1;
                                $transaction->voucher_id = $voucher->id;
                                $transaction->payment_type_id = $paymentModeType;
                                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                                $transaction->account_head_id = $lastTransaction->account_head_id;
                                $transaction->voucher_type = $request->voucher_type;
                                $transaction->transaction_type = $request->trnx_type_val[$key] == 1 ? 2 : 1;
                                $transaction->amount = $request->amount[$key];
                                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                                $transaction->voucher_no = $voucherNo;
                                $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                                $transaction->cheque_no = $request->cheque_no;
                                $transaction->notes = $request->notes;
                                $transaction->user_id = auth()->id();
                                $transaction->save();
                            }

                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->parent_account_head_id = $lastTransaction->account_head_id ?? null;
                            $transaction->other_deduct = $other_deduct;
                            $transaction->voucher_id = $voucher->id;
                            $transaction->payment_type_id = $paymentModeType;
                            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->cheque_no = $request->cheque_no;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();

                        }else{
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->other_deduct = $other_deduct;
                            $transaction->voucher_id = $voucher->id;
                            $transaction->payment_type_id = $paymentModeType;
                            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->cheque_no = $request->cheque_no;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }
                    }
                }

                if ($request->voucher_type != VoucherType::$JOURNAL_VOUCHER) {
                    if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                        //Credit
                        $transactionType = TransactionType::$CREDIT;
                    } elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                        //Debit
                        $transactionType = TransactionType::$DEBIT;
                    }
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->payment_type_id = $paymentModeType;
                    $transaction->payment_account_head_id = $paymentMode;
                    $transaction->account_head_id = $paymentMode;
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = $transactionType;
                    $transaction->amount = array_sum($request->amount) - ($otherDeductAmount + $otherDeductAmount);
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                    $transaction->cheque_no = $request->cheque_no;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    $voucher->amount = $transaction->amount;
                    $voucher->save();
                }
                $lastTransaction = Transaction::where('voucher_id',$voucher->id)
                    ->where('other_deduct',0)
                    ->where('except_edit',0)
                    ->where('account_head_id','!=',$paymentMode)
                    ->orderBy('id','desc')
                    ->first();

                if ($lastTransaction){
                    $lastTransaction->decrement('amount',$otherDeductAmount);
                }
            }else{
                foreach ($request->debit_account_head_id as $key => $requestAccount) {
                    //Debit
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = $request->debit_account_head_id[$key];
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $request->debit_amount[$key];
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                    $firstTransaction = $transaction;
                    //Credit
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->target_account_head_id = $firstTransaction->account_head_id;
                    $transaction->account_head_id = $request->credit_account_head_id[$key];
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $request->credit_amount[$key];
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    $firstTransaction->source_account_head_id = $transaction->account_head_id;
                    $firstTransaction->save();
                }
            }


            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('voucher.details', ['voucher' => $voucher, 'voucher_type' => $request->voucher_type])->with('success', $voucherTitle . ' created successfully');

        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->back()
                ->withInput()->with('error',  $voucherTitle . ': ' . $e->getMessage().' '.$e->getLine());
        }

    }

    public function edit(Voucher $voucher, Request $request)
    {
        abort_if($voucher->isMembershipCashout() || $voucher->isMembershipTransfer() || $voucher->isPackageSale(), 403, 'System payment vouchers are locked.');

        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
                ->get();
        $accountHeads = AccountHead::where('payment_mode',0)->where('is_control_account', 0)->get();
        $parties = AccountHead::whereNotNull('supplier_id')->get();
        $voucherType = $voucher->voucher_type;

        $isNotJournalVoucher = true;
        if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $partyLabel = 'Party';
            $isNotJournalVoucher = false;
            $permission = 'journal_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $partyLabel = 'Payee';
            $permission = 'payment_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $partyLabel = 'Depositor';
            $permission = 'receipt_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $partyLabel = 'Depositor';
            $permission = 'contra_voucher_edit';
            $accountHeads = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
            return view('accounts.receipt_payment.contra_edit', compact('paymentModes',
                'accountHeads', 'parties', 'voucherTitle', 'voucherType', 'voucher',
                'partyLabel', 'isNotJournalVoucher'));
        } else {
            $partyLabel = '';
            $permission = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
        abort_if($voucher->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        return view('accounts.receipt_payment.edit', compact('paymentModes',
            'accountHeads', 'parties', 'voucherTitle', 'voucherType', 'voucher',
            'partyLabel', 'isNotJournalVoucher'));
    }

    public function update(Voucher $voucher, Request $request)
    {
        abort_if($voucher->isMembershipCashout() || $voucher->isMembershipTransfer() || $voucher->isPackageSale(), 403, 'System payment vouchers are locked.');

        if ($voucher->voucher_type == '')
            abort('404');

        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $rules = [
                'account_head_id.*' => 'required',
                'amount.*' => 'required|numeric|min:.01',
                'payee_or_depositor' => 'nullable',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $rules = [
                'debit_account_head_id.*' => 'required',
                'credit_account_head_id.*' => 'required',
                'debit_amount.*' => 'required|numeric|min:.01',
                'credit_amount.*' => 'required|numeric|min:.01',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        }else {
            $rules = [
                'account_head_id.*' => 'required',
                'amount.*' => 'required|numeric|min:.01',
                'payment_mode' => 'required',
                'payee_or_depositor' => 'nullable',
                'date' => 'required|date',
                'notes' => 'nullable|max:255',
            ];
        }
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            $voucherTitle = 'Journal Voucher';
            $permission = 'journal_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
            $voucherTitle = 'Payment Voucher';
            $permission = 'payment_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
            $voucherTitle = 'Receipt Voucher';
            $permission = 'receipt_voucher_edit';
        } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $voucherTitle = 'Contra Voucher';
            $permission = 'contra_voucher_edit';
        } else {
            $permission = '';
            return redirect()->route('dashboard')->with('error', 'Something wrong..');
        }
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
        abort_if($voucher->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

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
        $debitTotal = 0;
        $creditTotal = 0;
        $total = 0;
        if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
            foreach ($request->account_head_id as $key => $requestAccount) {
                if ($request->trnx_type_val[$key] == TransactionType::$DEBIT) {
                    $debitTotal += $request->amount[$key];
                    $total += $request->amount[$key];
                } else {
                    $creditTotal += $request->amount[$key];
                }
            }
            if ($debitTotal != $creditTotal) {
                return redirect()
                    ->route('voucher.create', ['voucher_type' => $request->voucher_type])
                    ->withInput()->with('error', 'For journal voucher debit and credit amount must be same');
            }
        } elseif ($request->voucher_type == VoucherType::$CONTRA_VOUCHER) {
            $debitTotal = array_sum($request->debit_amount);
            $creditTotal = array_sum($request->credit_amount);
            $total = $debitTotal;
            if ($debitTotal != $creditTotal) {
                return redirect()
                    ->route('voucher.create', ['voucher_type' => $request->voucher_type])
                    ->withInput()->with('error', 'For contra voucher debit and credit amount must be same');
            }
        } else {
            $total = array_sum($request->amount);
        }
        // Start a database transaction
        DB::beginTransaction();

        try {
            $voucherNoGroupSl = $voucher->voucher_no_group_sl;
            $voucherNo = $voucher->voucher_no;

            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            $voucher->amount = $total;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $request->payee_or_depositor;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->user_id = auth()->id();
            $voucher->save();

            Transaction::where('voucher_id', $voucher->id)->delete();

            if ($request->voucher_type != VoucherType::$CONTRA_VOUCHER){
                $otherDeductAmount = 0;
                foreach ($request->account_head_id as $key => $requestAccount) {

                    if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                        //Debit
                        $transactionType = TransactionType::$DEBIT;
                    }elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                        //Credit
                        $transactionType = TransactionType::$CREDIT;
                    }
                    if ($request->voucher_type == VoucherType::$JOURNAL_VOUCHER){
                        if ($request->trnx_type_val[$key] == 1){
                            //Debit
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->voucher_id = $voucher->id;
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }else{
                            //Credit
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->voucher_id = $voucher->id;
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }

                    }else{

                        //$request->trnx_type_val = 1 Debit,$request->trnx_type_val[$key] = 2 Credit

                        $other_deduct = 0;
                        if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER){
                            if ($request->trnx_type_val[$key] == 2){
                                $other_deduct = 1;
                            }
                        }else{
                            if ($request->trnx_type_val[$key] == 1){
                                $other_deduct = 1;
                            }
                        }
                        if ($other_deduct == 1){
                            //If any other Deduction
                            $otherDeductAmount +=$request->amount[$key];

                            $lastTransaction = Transaction::where('voucher_id',$voucher->id)
                                ->where('other_deduct',0)
                                ->where('account_head_id','!=',$paymentMode)
                                ->orderBy('id','desc')
                                ->first();
                            if ($lastTransaction){

                                $transaction = new Transaction();
                                $transaction->sister_concern_id = auth()->user()->activeConcernId();
                                $transaction->except_edit = 1;
                                $transaction->voucher_id = $voucher->id;
                                $transaction->payment_type_id = $paymentModeType;
                                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                                $transaction->account_head_id = $lastTransaction->account_head_id;
                                $transaction->voucher_type = $request->voucher_type;
                                $transaction->transaction_type = $request->trnx_type_val[$key] == 1 ? 2 : 1;
                                $transaction->amount = $request->amount[$key];
                                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                                $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                                $transaction->voucher_no = $voucherNo;
                                $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                                $transaction->cheque_no = $request->cheque_no;
                                $transaction->notes = $request->notes;
                                $transaction->user_id = auth()->id();
                                $transaction->save();
                            }

                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->parent_account_head_id = $lastTransaction->account_head_id ?? null;
                            $transaction->other_deduct = $other_deduct;
                            $transaction->voucher_id = $voucher->id;
                            $transaction->payment_type_id = $paymentModeType;
                            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->cheque_no = $request->cheque_no;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();

                        }else{
                            $transaction = new Transaction();
                            $transaction->sister_concern_id = auth()->user()->activeConcernId();
                            $transaction->other_deduct = $other_deduct;
                            $transaction->voucher_id = $voucher->id;
                            $transaction->payment_type_id = $paymentModeType;
                            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
                            $transaction->account_head_id = $request->account_head_id[$key];
                            $transaction->voucher_type = $request->voucher_type;
                            $transaction->transaction_type = $request->trnx_type_val[$key];
                            $transaction->amount = $request->amount[$key];
                            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                            $transaction->voucher_no = $voucherNo;
                            $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                            $transaction->cheque_no = $request->cheque_no;
                            $transaction->notes = $request->notes;
                            $transaction->user_id = auth()->id();
                            $transaction->save();
                        }
                    }
                }

                if ($request->voucher_type != VoucherType::$JOURNAL_VOUCHER) {
                    if ($request->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                        //Credit
                        $transactionType = TransactionType::$CREDIT;
                    } elseif ($request->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                        //Debit
                        $transactionType = TransactionType::$DEBIT;
                    }
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->payment_type_id = $paymentModeType;
                    $transaction->payment_account_head_id = $paymentMode;
                    $transaction->account_head_id = $paymentMode;
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = $transactionType;
                    $transaction->amount = array_sum($request->amount) - ($otherDeductAmount + $otherDeductAmount);
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->account_head_payee_depositor_id = $request->payee_or_depositor;
                    $transaction->cheque_no = $request->cheque_no;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    $voucher->amount = $transaction->amount;
                    $voucher->save();
                }

                $lastTransaction = Transaction::where('voucher_id',$voucher->id)
                    ->where('other_deduct',0)
                    ->where('except_edit',0)
                    ->where('account_head_id','!=',$paymentMode)
                    ->orderBy('id','desc')
                    ->first();

                if ($lastTransaction){
                    $lastTransaction->decrement('amount',$otherDeductAmount);
                }
            }else{
                foreach ($request->debit_account_head_id as $key => $requestAccount) {
                    //Debit
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = $request->debit_account_head_id[$key];
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = TransactionType::$DEBIT;
                    $transaction->amount = $request->debit_amount[$key];
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();

                    //Credit
                    $transaction = new Transaction();
                    $transaction->sister_concern_id = auth()->user()->activeConcernId();
                    $transaction->voucher_id = $voucher->id;
                    $transaction->account_head_id = $request->credit_account_head_id[$key];
                    $transaction->voucher_type = $request->voucher_type;
                    $transaction->transaction_type = TransactionType::$CREDIT;
                    $transaction->amount = $request->credit_amount[$key];
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->voucher_no_group_sl = $voucherNoGroupSl;
                    $transaction->voucher_no = $voucherNo;
                    $transaction->notes = $request->notes;
                    $transaction->user_id = auth()->id();
                    $transaction->save();
                }
            }

            // Commit the transaction
            DB::commit();
            // Redirect to the index page with a success message
            return redirect()->route('voucher.details', ['voucher' => $voucher, 'voucher_type' => $request->voucher_type])->with('success', $voucherTitle . ' updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('voucher.edit', ['voucher' => $voucher->id, 'voucher_type' => $request->voucher_type])->withInput()->with('error', 'An error occurred while updating the ' . $voucherTitle . ': ' . $e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        abort_if($voucher->isMembershipCashout() || $voucher->isMembershipTransfer() || $voucher->isPackageSale(), 403, 'System payment vouchers are locked.');
        try {
            $permissionDelete = '';
            if ($voucher->voucher_type == VoucherType::$JOURNAL_VOUCHER) {
                $permissionDelete = 'journal_voucher_delete';
            } elseif ($voucher->voucher_type == VoucherType::$PAYMENT_VOUCHER) {
                $permissionDelete = 'payment_voucher_delete';
            } elseif ($voucher->voucher_type == VoucherType::$COLLECTION_VOUCHER) {
                $permissionDelete = 'receipt_voucher_delete';
            } elseif ($voucher->voucher_type == VoucherType::$CONTRA_VOUCHER) {
                $permissionDelete = 'contra_voucher_delete';
            }
            abort_if(!auth()->user()->hasPermissionTo($permissionDelete), 403, 'Unauthorized');
            $log = Transaction::where('voucher_id', $voucher->id)->first();

            if ($voucher->voucher_type ==  VoucherType::$PAYMENT_VOUCHER){
                $purchaseOrder = PurchaseOrder::where('id', $voucher->purchase_order_id)->first();
                if ($purchaseOrder) {
                    $purchaseOrder->increment('due', $voucher->amount);
                    $purchaseOrder->decrement('paid', $voucher->amount);
                }
            }
            if ($voucher->voucher_type ==  VoucherType::$COLLECTION_VOUCHER){
                $salesOrder = SaleOrder::where('id', $voucher->sale_order_id)->first();
                if ($salesOrder) {
                    $salesOrder->increment('due', $voucher->amount);
                    $salesOrder->decrement('paid', $voucher->amount);
                }
            }

            $inventoryLog = InventoryLog::where('id', $voucher->inventory_log_id)->first();
            //if utilize then reset stock and delete stock log
            if ($inventoryLog) {
                if ($inventoryLog->inventory) {
                    $inventoryLog->inventory->increment('quantity', $inventoryLog->quantity);
                }
                $inventoryLog->delete();
            }

            if ($log) {
                Transaction::where('voucher_id', $voucher->id)->delete();
            }
            // Delete the Voucher record
            $voucher->delete();
            // Return a JSON success response
            return response()->json(['success' => true, 'message' => 'Voucher deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => 'Voucher not found: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }
}
