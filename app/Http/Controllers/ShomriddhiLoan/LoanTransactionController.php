<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiLoan\LoanAccount;
use App\Models\ShomriddhiLoan\LoanInstallment;
use App\Models\ShomriddhiLoan\LoanTransaction;
use App\Models\Account\AccountHead;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LoanTransactionController extends Controller
{
    public function index()
    {
        return view('shomriddhi_loan.collections.index');
    }

    public function dataTable()
    {
        $query = LoanTransaction::with(['loanAccount.customer', 'collectedBy', 'voucher'])->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('collection_date', function (LoanTransaction $txn) {
                return date('d M Y', strtotime($txn->collection_date));
            })
            ->addColumn('loan_no', function (LoanTransaction $txn) {
                if ($txn->loanAccount) {
                    return '<a href="' . route('shomriddhi-loan.accounts.show', $txn->loanAccount->id) . '">' . $txn->loanAccount->loan_no . '</a>';
                }
                return 'N/A';
            })
            ->addColumn('customer', function (LoanTransaction $txn) {
                return $txn->loanAccount->customer->name ?? 'N/A';
            })
            ->addColumn('voucher', function (LoanTransaction $txn) {
                return $txn->voucher->voucher_no ?? 'N/A';
            })
            ->addColumn('collected_by', function (LoanTransaction $txn) {
                return $txn->collectedBy->name ?? 'N/A';
            })
            ->editColumn('amount', function (LoanTransaction $txn) {
                return '৳ ' . number_format($txn->amount, 2);
            })
            ->rawColumns(['loan_no'])
            ->toJson();
    }

    public function create($account_id = null)
    {
        $loan = null;
        if ($account_id) {
            $loan = LoanAccount::with(['customer', 'installments' => function($q) {
                $q->whereIn('status', ['unpaid', 'partial', 'overdue'])->orderBy('due_date', 'asc');
            }])->findOrFail($account_id);
        }
        
        $activeLoans = LoanAccount::with('customer')->where('status', 'active')->get();
        $paymentModes = AccountHead::where('payment_mode', '>', 0)->where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->get();
        
        return view('shomriddhi_loan.collections.create', compact('loan', 'activeLoans', 'paymentModes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_account_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'collection_date' => 'required|date',
            'payment_mode' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $loan = LoanAccount::findOrFail($request->loan_account_id);
            $amountToDistribute = $request->amount;
            
            // Check if amount exceeds remaining due
            $remainingDue = $loan->total_payable - $loan->paid_amount;
            if ($amountToDistribute > $remainingDue) {
                throw new \Exception("Collection amount cannot exceed the remaining due amount of ৳ " . number_format($remainingDue, 2));
            }

            // 1. Create Transaction Record
            $transaction = LoanTransaction::create([
                'loan_account_id' => $loan->id,
                'origin_sister_concern_id' => $loan->origin_sister_concern_id,
                'amount' => $amountToDistribute,
                'collection_date' => $request->collection_date,
                'payment_mode' => explode('|', $request->payment_mode)[1] ?? 'Cash', // Assuming id|name format
                'collected_by' => auth()->id(),
                'notes' => $request->notes
            ]);

            // 2. FIFO Allocation to Installments
            $pendingInstallments = LoanInstallment::where('loan_account_id', $loan->id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->orderBy('due_date', 'asc')
                ->get();

            foreach ($pendingInstallments as $installment) {
                if ($amountToDistribute <= 0) break;

                $installmentDue = $installment->total_amount - $installment->paid_amount;
                
                if ($amountToDistribute >= $installmentDue) {
                    // Fully pay this installment
                    $installment->paid_amount = $installment->total_amount;
                    $installment->status = 'paid';
                    $amountToDistribute -= $installmentDue;
                } else {
                    // Partially pay this installment
                    $installment->paid_amount += $amountToDistribute;
                    $installment->status = 'partial';
                    $amountToDistribute = 0;
                }
                $installment->save();
            }

            // 3. Update main loan account
            $loan->paid_amount += $request->amount;
            if ($loan->paid_amount >= $loan->total_payable) {
                $loan->status = 'completed';
            }
            $loan->save();

            // 4. Accounting Entries (General Ledger)
            $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $loan->customer_id)->first();
            $payeeId = $customerAccountHead ? $customerAccountHead->id : null;

            if ($payeeId) {
                $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
                
                $pVoucher = new Voucher();
                $pVoucher->sister_concern_id = auth()->user()->activeConcernId();
                $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $pVoucher->payment_type_id = AccountHead::find(explode('|', $request->payment_mode)[0] ?? $request->payment_mode)->payment_mode ?? null;
                $pVoucher->payment_account_head_id = explode('|', $request->payment_mode)[0] ?? $request->payment_mode; // Receipt Mode
                $pVoucher->amount = $request->amount;
                $pVoucher->date = \Carbon\Carbon::parse($request->collection_date)->format('Y-m-d');
                $pVoucher->voucher_no_group_sl = $pNoGroupSl;
                $pVoucher->voucher_no = $pNo;
                $pVoucher->account_head_payee_depositor_id = $payeeId;
                if ($loan->sale_order_id) {
                    $pVoucher->sale_order_id = $loan->sale_order_id;
                }
                $pVoucher->user_id = auth()->id();
                $pVoucher->save();
                
                $transaction->voucher_id = $pVoucher->id;
                $transaction->save();

                // Debit Cash/Bank
                $paymentMethodId = explode('|', $request->payment_mode)[0] ?? $request->payment_mode;
                $this->createTransaction($pVoucher, $paymentMethodId, TransactionType::$DEBIT, $request->amount, $loan->sale_order_id);
                
                // Credit Customer
                $this->createTransaction($pVoucher, $payeeId, TransactionType::$CREDIT, $request->amount, $loan->sale_order_id);
            }
            
            DB::commit();
            return redirect()->route('shomriddhi-loan.accounts.show', $loan->id)->with('success', 'Collection processed successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function createTransaction($voucher, $headId, $type, $amount, $saleOrderId)
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
        $transaction->user_id = auth()->id();
        $transaction->save();
    }
}
