<?php

namespace App\Http\Controllers\Account;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Account\InterCompanySettlement;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\BranchManagement\SisterConcern;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InterCompanySettlementController extends Controller
{
    public function index(Request $request)
    {
        return view('accounts.inter_company_settlements.index');
    }

    public function dataTable()
    {
        $query = InterCompanySettlement::where('from_sister_concern_id', auth()->user()->activeConcernId())
            ->with('toSisterConcern', 'fromAccountHead');

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (InterCompanySettlement $settlement) {
                $btn = '';
                if ($settlement->status == 1) {
                    $btn .= '<form action="' . route('inter_company_settlements.cancel', $settlement->id) . '" method="post" class="d-inline form-cancel">';
                    $btn .= csrf_field();
                    $btn .= '<button type="button" class="btn btn-danger btn-sm btn-cancel"><i class="fa fa-times"></i> Cancel</button>';
                    $btn .= '</form>';
                }
                return $btn;
            })
            ->addColumn('status', function (InterCompanySettlement $settlement) {
                if ($settlement->status == 1) return '<span class="badge badge-warning">Pending Receipt</span>';
                if ($settlement->status == 2) return '<span class="badge badge-success">Received</span>';
                if ($settlement->status == 3) return '<span class="badge badge-danger">Cancelled/Rejected</span>';
                return '';
            })
            ->addColumn('amount', function (InterCompanySettlement $settlement) {
                return number_format($settlement->amount, 2);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        $activeConcernId = auth()->user()->activeConcernId();
        
        $branches = SisterConcern::where('status', 1)
            ->where('id', '!=', $activeConcernId)
            ->get();

        $cashBankHeads = AccountHead::where(function($q) use ($activeConcernId) {
                $q->whereNull('sister_concern_id')
                  ->orWhere('sister_concern_id', $activeConcernId);
            })
            ->where('payment_mode', '>', 0)
            ->where('status', 1)
            ->get();

        return view('accounts.inter_company_settlements.create', compact('branches', 'cashBankHeads'));
    }
    
    public function getPayableBalance(Request $request)
    {
        $activeConcernId = auth()->user()->activeConcernId();
        $targetConcernId = $request->target_concern_id;
        
        $targetSc = SisterConcern::find($targetConcernId);
        
        // Find payable head
        $payableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                    ->where('name', 'like', 'Inter-company Payable - ' . ($targetSc->code ?? sprintf('%03d', $targetSc->id)) . '%')
                    ->whereNull('sister_concern_id')
                    ->first();
                    
        if (!$payableHead) {
            return response()->json(['success' => true, 'balance' => 0]);
        }
        
        // Calculate balance
        $credit = Transaction::where('account_head_id', $payableHead->id)
                    ->where('sister_concern_id', $activeConcernId)
                    ->where('transaction_type', TransactionType::$CREDIT)
                    ->sum('amount');
                    
        $debit = Transaction::where('account_head_id', $payableHead->id)
                    ->where('sister_concern_id', $activeConcernId)
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->sum('amount');
                    
        $balance = $credit - $debit; // payable is credit nature
        
        return response()->json(['success' => true, 'balance' => $balance > 0 ? $balance : 0]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_sister_concern_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'from_account_head_id' => 'required',
            'date' => 'required|date',
        ]);

        $activeConcernId = auth()->user()->activeConcernId();
        $targetConcernId = $request->to_sister_concern_id;
        $targetSc = SisterConcern::find($targetConcernId);

        DB::beginTransaction();
        try {
            $payableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                    ->where('name', 'like', 'Inter-company Payable - ' . ($targetSc->code ?? sprintf('%03d', $targetSc->id)) . '%')
                    ->whereNull('sister_concern_id')
                    ->first();
                    
            if (!$payableHead) {
                throw new \Exception("No Payable Account Head found for this branch.");
            }

            $credit = Transaction::where('account_head_id', $payableHead->id)
                        ->where('sister_concern_id', $activeConcernId)
                        ->where('transaction_type', TransactionType::$CREDIT)
                        ->sum('amount');
                        
            $debit = Transaction::where('account_head_id', $payableHead->id)
                        ->where('sister_concern_id', $activeConcernId)
                        ->where('transaction_type', TransactionType::$DEBIT)
                        ->sum('amount');
                        
            $balance = $credit - $debit;

            if ($request->amount > $balance) {
                throw new \Exception("Payment amount cannot exceed the payable balance (" . number_format($balance, 2) . ")");
            }

            // Check cash/bank balance
            $fromAccount = AccountHead::find($request->from_account_head_id);
            // if (!$fromAccount->isSufficient($request->amount, $activeConcernId)) {
            //     $cashBalance = $fromAccount->getCurrentBalance($activeConcernId);
            //     // throw new \Exception("Insufficient balance in the selected Cash/Bank account. Available Balance: " . number_format($cashBalance, 2));
            // }

            // Create Sender Voucher
            $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER, null, $activeConcernId);
            $voucherNoSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = new Voucher();
            $voucher->sister_concern_id = $activeConcernId;
            $voucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $voucher->amount = $request->amount;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoSl;
            $voucher->account_head_payee_depositor_id = $request->from_account_head_id; // Bank/Cash
            $voucher->notes = 'Inter-company Payment to ' . $targetSc->name . ($request->notes ? ' - ' . $request->notes : '');
            $voucher->user_id = auth()->id();
            $voucher->save();

            // Debit Payable
            $tx1 = new Transaction();
            $tx1->sister_concern_id = $activeConcernId;
            $tx1->account_head_id = $payableHead->id;
            $tx1->voucher_id = $voucher->id;
            $tx1->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $tx1->transaction_type = TransactionType::$DEBIT;
            $tx1->amount = $request->amount;
            $tx1->date = $voucher->date;
            $tx1->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
            $tx1->voucher_no = $voucherNo;
            $tx1->voucher_no_group_sl = $voucherNoSl;
            $tx1->notes = $voucher->notes;
            $tx1->user_id = auth()->id();
            $tx1->save();

            // Credit Cash/Bank
            $tx2 = new Transaction();
            $tx2->sister_concern_id = $activeConcernId;
            $tx2->account_head_id = $request->from_account_head_id;
            $tx2->voucher_id = $voucher->id;
            $tx2->voucher_type = VoucherType::$PAYMENT_VOUCHER;
            $tx2->transaction_type = TransactionType::$CREDIT;
            $tx2->amount = $request->amount;
            $tx2->date = $voucher->date;
            $tx2->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
            $tx2->voucher_no = $voucherNo;
            $tx2->voucher_no_group_sl = $voucherNoSl;
            $tx2->notes = $voucher->notes;
            $tx2->user_id = auth()->id();
            $tx2->save();


            // Save Settlement
            $settlement = new InterCompanySettlement();
            $settlement->payment_no = 'ICP-' . time();
            $settlement->from_sister_concern_id = $activeConcernId;
            $settlement->to_sister_concern_id = $targetConcernId;
            $settlement->date = $voucher->date;
            $settlement->amount = $request->amount;
            $settlement->from_account_head_id = $request->from_account_head_id;
            $settlement->sender_voucher_id = $voucher->id;
            $settlement->notes = $request->notes;
            $settlement->status = 1; // Pending receipt by target branch
            $settlement->create_user_id = auth()->id();
            $settlement->save();

            DB::commit();
            return redirect()->route('inter_company_settlements.index')->with('success', 'Payment dispatched successfully to ' . $targetSc->name);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function receiveList()
    {
        return view('accounts.inter_company_settlements.receive_list');
    }

    public function receiveDataTable()
    {
        $query = InterCompanySettlement::where('to_sister_concern_id', auth()->user()->activeConcernId())
            ->with('fromSisterConcern');

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (InterCompanySettlement $settlement) {
                if ($settlement->status == 1) {
                    return '<button class="btn btn-primary btn-sm btn-receive" data-id="' . $settlement->id . '"><i class="fa fa-download"></i> Receive</button>
                            <button class="btn btn-danger btn-sm btn-reject" data-id="' . $settlement->id . '"><i class="fa fa-times"></i> Reject</button>';
                }
                return '<span class="badge badge-success">Completed</span>';
            })
            ->addColumn('status', function (InterCompanySettlement $settlement) {
                if ($settlement->status == 1) return '<span class="badge badge-warning">Pending</span>';
                if ($settlement->status == 2) return '<span class="badge badge-success">Received</span>';
                if ($settlement->status == 3) return '<span class="badge badge-danger">Cancelled</span>';
                return '';
            })
            ->addColumn('amount', function (InterCompanySettlement $settlement) {
                return number_format($settlement->amount, 2);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    
    public function receiveStore(Request $request)
    {
        $request->validate([
            'settlement_id' => 'required',
            'to_account_head_id' => 'required', // Receive to which bank/cash
        ]);

        $settlement = InterCompanySettlement::find($request->settlement_id);
        if (!$settlement || $settlement->status != 1 || $settlement->to_sister_concern_id != auth()->user()->activeConcernId()) {
            return response()->json(['success' => false, 'message' => 'Invalid or already processed settlement']);
        }

        $activeConcernId = auth()->user()->activeConcernId();
        $senderSc = SisterConcern::find($settlement->from_sister_concern_id);

        DB::beginTransaction();
        try {
            $receivableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)
                    ->where('name', 'like', 'Inter-company Receivable - ' . ($senderSc->code ?? sprintf('%03d', $senderSc->id)) . '%')
                    ->whereNull('sister_concern_id')
                    ->first();
                    
            if (!$receivableHead) {
                throw new \Exception("No Receivable Account Head found for this branch.");
            }

            // Create Receiver Voucher
            $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER, null, $activeConcernId);
            $voucherNoSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = new Voucher();
            $voucher->sister_concern_id = $activeConcernId;
            $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $voucher->amount = $settlement->amount;
            $voucher->date = Carbon::now()->format('Y-m-d');
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoSl;
            $voucher->account_head_payee_depositor_id = $request->to_account_head_id;
            $voucher->notes = 'Inter-company Receipt from ' . $senderSc->name . ($settlement->notes ? ' - ' . $settlement->notes : '');
            $voucher->user_id = auth()->id();
            $voucher->save();

            // Debit Cash/Bank
            $tx1 = new Transaction();
            $tx1->sister_concern_id = $activeConcernId;
            $tx1->account_head_id = $request->to_account_head_id;
            $tx1->voucher_id = $voucher->id;
            $tx1->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $tx1->transaction_type = TransactionType::$DEBIT;
            $tx1->amount = $settlement->amount;
            $tx1->date = $voucher->date;
            $tx1->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
            $tx1->voucher_no = $voucherNo;
            $tx1->voucher_no_group_sl = $voucherNoSl;
            $tx1->notes = $voucher->notes;
            $tx1->user_id = auth()->id();
            $tx1->save();

            // Credit Receivable
            $tx2 = new Transaction();
            $tx2->sister_concern_id = $activeConcernId;
            $tx2->account_head_id = $receivableHead->id;
            $tx2->voucher_id = $voucher->id;
            $tx2->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $tx2->transaction_type = TransactionType::$CREDIT;
            $tx2->amount = $settlement->amount;
            $tx2->date = $voucher->date;
            $tx2->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
            $tx2->voucher_no = $voucherNo;
            $tx2->voucher_no_group_sl = $voucherNoSl;
            $tx2->notes = $voucher->notes;
            $tx2->user_id = auth()->id();
            $tx2->save();

            // Update Settlement
            $settlement->to_account_head_id = $request->to_account_head_id;
            $settlement->receiver_voucher_id = $voucher->id;
            $settlement->status = 2; // Received
            $settlement->received_user_id = auth()->id();
            $settlement->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment received successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rejectStore(Request $request)
    {
        $request->validate([
            'settlement_id' => 'required',
            'reject_reason' => 'required|string|max:255'
        ]);

        $settlement = InterCompanySettlement::findOrFail($request->settlement_id);

        if ($settlement->status != 1) {
            return response()->json(['success' => false, 'message' => 'Only pending settlements can be rejected.']);
        }

        DB::beginTransaction();
        try {
            // Reversal logic for the sender
            // 1. We need to find the sender's voucher
            $senderVoucher = Voucher::with('transactions')->find($settlement->sender_voucher_id);
            if (!$senderVoucher) {
                throw new \Exception("Original sender voucher not found for reversal.");
            }

            // Create Reversal Voucher in sender's branch
            $reversalVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $settlement->from_sister_concern_id);
            $reversalVoucherNoSl = abs((int)filter_var($reversalVoucherNo, FILTER_SANITIZE_NUMBER_INT));

            $revVoucher = new Voucher();
            $revVoucher->sister_concern_id = $settlement->from_sister_concern_id;
            $revVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $revVoucher->amount = $settlement->amount;
            $revVoucher->date = Carbon::now()->format('Y-m-d');
            $revVoucher->voucher_no = $reversalVoucherNo;
            $revVoucher->voucher_no_group_sl = $reversalVoucherNoSl;
            $revVoucher->notes = "Reversal of Settlement #$settlement->payment_no. Reason: " . $request->reject_reason;
            $revVoucher->user_id = auth()->id();
            $revVoucher->save();

            // Reverse the original transactions (Debit -> Credit, Credit -> Debit)
            foreach ($senderVoucher->transactions as $origTx) {
                $revTx = new Transaction();
                $revTx->sister_concern_id = $origTx->sister_concern_id;
                $revTx->account_head_id = $origTx->account_head_id;
                $revTx->voucher_id = $revVoucher->id;
                $revTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                
                // Swap transaction type
                $revTx->transaction_type = ($origTx->transaction_type == TransactionType::$DEBIT) 
                                           ? TransactionType::$CREDIT 
                                           : TransactionType::$DEBIT;
                                           
                $revTx->amount = $origTx->amount;
                $revTx->date = $revVoucher->date;
                $revTx->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
                $revTx->voucher_no = $reversalVoucherNo;
                $revTx->voucher_no_group_sl = $reversalVoucherNoSl;
                $revTx->notes = $revVoucher->notes;
                $revTx->user_id = auth()->id();
                $revTx->save();
            }

            // Update Settlement Status
            $settlement->status = 3; // Rejected
            $settlement->notes = trim($settlement->notes . " | Rejected Reason: " . $request->reject_reason);
            $settlement->received_user_id = auth()->id(); // User who rejected it
            $settlement->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Settlement rejected and reversed successfully.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancel($id)
    {
        $settlement = InterCompanySettlement::findOrFail($id);

        if ($settlement->status != 1) {
            return redirect()->back()->with('error', 'Only pending settlements can be cancelled.');
        }

        DB::beginTransaction();
        try {
            // We need to find the sender's voucher
            $senderVoucher = Voucher::with('transactions')->find($settlement->sender_voucher_id);
            if (!$senderVoucher) {
                throw new \Exception("Original sender voucher not found for reversal.");
            }

            // Create Reversal Voucher in sender's branch
            $reversalVoucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER, null, $settlement->from_sister_concern_id);
            $reversalVoucherNoSl = abs((int)filter_var($reversalVoucherNo, FILTER_SANITIZE_NUMBER_INT));

            $revVoucher = new Voucher();
            $revVoucher->sister_concern_id = $settlement->from_sister_concern_id;
            $revVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $revVoucher->amount = $settlement->amount;
            $revVoucher->date = Carbon::now()->format('Y-m-d');
            $revVoucher->voucher_no = $reversalVoucherNo;
            $revVoucher->voucher_no_group_sl = $reversalVoucherNoSl;
            $revVoucher->notes = "Cancellation of Settlement #$settlement->payment_no by sender.";
            $revVoucher->user_id = auth()->id();
            $revVoucher->save();

            // Reverse the original transactions (Debit -> Credit, Credit -> Debit)
            foreach ($senderVoucher->transactions as $origTx) {
                $revTx = new Transaction();
                $revTx->sister_concern_id = $origTx->sister_concern_id;
                $revTx->account_head_id = $origTx->account_head_id;
                $revTx->voucher_id = $revVoucher->id;
                $revTx->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                
                // Swap transaction type
                $revTx->transaction_type = ($origTx->transaction_type == TransactionType::$DEBIT) 
                                           ? TransactionType::$CREDIT 
                                           : TransactionType::$DEBIT;
                                           
                $revTx->amount = $origTx->amount;
                $revTx->date = $revVoucher->date;
                $revTx->payment_purpose = PaymentPurpose::$INTER_COMPANY_SETTLEMENT;
                $revTx->voucher_no = $reversalVoucherNo;
                $revTx->voucher_no_group_sl = $reversalVoucherNoSl;
                $revTx->notes = $revVoucher->notes;
                $revTx->user_id = auth()->id();
                $revTx->save();
            }

            // Update Settlement Status
            $settlement->status = 3; // Cancelled
            $settlement->notes = trim($settlement->notes . " | Cancelled by sender.");
            $settlement->save();

            DB::commit();
            return redirect()->back()->with('success', 'Settlement cancelled successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
