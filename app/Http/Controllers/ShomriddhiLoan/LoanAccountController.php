<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiLoan\LoanAccount;
use App\Models\ShomriddhiLoan\LoanInstallment;
use App\Models\Supplier;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\AccountHead;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LoanAccountController extends Controller
{
    public function index()
    {
        $paymentModes = AccountHead::where('payment_mode', '>', 0)->where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->get();
        return view('shomriddhi_loan.accounts.index', compact('paymentModes'));
    }

    public function dataTable()
    {
        $query = LoanAccount::with(['customer'])->latest();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('customer', function (LoanAccount $loan) {
                return $loan->customer->name ?? 'N/A';
            })
            ->editColumn('type', function (LoanAccount $loan) {
                return '<span class="text-capitalize">' . $loan->type . '</span>';
            })
            ->editColumn('principal_amount', function (LoanAccount $loan) {
                return '৳ ' . number_format($loan->principal_amount, 2);
            })
            ->editColumn('total_payable', function (LoanAccount $loan) {
                return '৳ ' . number_format($loan->total_payable, 2);
            })
            ->editColumn('paid_amount', function (LoanAccount $loan) {
                return '৳ ' . number_format($loan->paid_amount, 2);
            })
            ->editColumn('status', function (LoanAccount $loan) {
                if ($loan->status == 'pending')
                    return '<span class="badge badge-warning">Pending</span>';
                if ($loan->status == 'active')
                    return '<span class="badge badge-success">Active</span>';
                return '<span class="badge badge-secondary">' . ucfirst($loan->status) . '</span>';
            })
            ->addColumn('action', function (LoanAccount $loan) {
                $btn = '<a href="' . route('shomriddhi-loan.accounts.show', $loan->id) . '" class="btn btn-info btn-xs m-1"><i class="fas fa-eye"></i> View</a>';
                if ($loan->status == 'pending' && $loan->type == 'cash') {
                    $btn .= ' <button type="button" class="btn btn-success btn-xs btn-approve m-1" data-id="' . $loan->id . '" data-loan="' . $loan->loan_no . '"><i class="fas fa-check"></i> Approve</button>';
                }
                return $btn;
            })
            ->rawColumns(['type', 'status', 'action'])
            ->toJson();
    }

    public function create()
    {
        $customers = Supplier::where('status', 1)->get();
        $sisterConcerns = SisterConcern::where('status', 1)->get();
        return view('shomriddhi_loan.accounts.create', compact('customers', 'sisterConcerns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'type' => 'required|in:cash', // Product loans are created via EMI sales
            'principal_amount' => 'required|numeric|min:0',
            'profit_rate' => 'required|numeric|min:0',
            'installment_type' => 'required|in:daily,weekly,monthly',
            'total_installments' => 'required|integer|min:1',
            'disbursement_date' => 'required|date',
            'origin_sister_concern_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $total_payable = $request->principal_amount + ($request->principal_amount * $request->profit_rate / 100);
            $installment_amount = $total_payable / $request->total_installments;

            $loan = LoanAccount::create([
                'loan_no' => 'SLN-' . date('Ym') . '-' . rand(1000, 9999),
                'customer_id' => $request->customer_id,
                'origin_sister_concern_id' => $request->origin_sister_concern_id,
                'type' => $request->type,
                'principal_amount' => $request->principal_amount,
                'profit_rate' => $request->profit_rate,
                'total_payable' => $total_payable,
                'installment_type' => $request->installment_type,
                'total_installments' => $request->total_installments,
                'installment_amount' => $installment_amount,
                'disbursement_date' => $request->disbursement_date,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Generate Schedule
            $this->generateSchedule($loan);

            DB::commit();
            return redirect()->route('shomriddhi-loan.accounts.index')->with('success', 'Loan Account Created Successfully. Awaiting Approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function generateSchedule(LoanAccount $loan)
    {
        $dueDate = Carbon::parse($loan->disbursement_date);
        $principalPerInst = $loan->principal_amount / $loan->total_installments;
        $profitPerInst = ($loan->total_payable - $loan->principal_amount) / $loan->total_installments;

        for ($i = 1; $i <= $loan->total_installments; $i++) {
            
            // Increment due date based on type
            if ($loan->installment_type == 'daily') {
                $dueDate->addDay();
            } elseif ($loan->installment_type == 'weekly') {
                $dueDate->addWeek();
            } elseif ($loan->installment_type == 'monthly') {
                $dueDate->addMonth();
            }

            LoanInstallment::create([
                'loan_account_id' => $loan->id,
                'installment_no' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'principal_component' => $principalPerInst,
                'profit_component' => $profitPerInst,
                'total_amount' => $loan->installment_amount,
                'status' => 'unpaid'
            ]);
        }
    }

    public function show($id)
    {
        $loan = LoanAccount::with(['customer', 'installments', 'transactions'])->findOrFail($id);
        return view('shomriddhi_loan.accounts.show', compact('loan'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required'
        ]);

        $loan = LoanAccount::findOrFail($id);
        if ($loan->status == 'pending' && $loan->type == 'cash') {
            DB::beginTransaction();
            try {
                $loan->update([
                    'status' => 'active',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
                
                // GL Accounting entry for Cash Loan Disbursement
                $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $loan->customer_id)->first();
                $payeeId = $customerAccountHead ? $customerAccountHead->id : null;
                
                if ($payeeId) {
                    $pNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
                    $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
                    
                    $pVoucher = new Voucher();
                    $pVoucher->sister_concern_id = auth()->user()->activeConcernId();
                    $pVoucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
                    $pVoucher->payment_type_id = AccountHead::find($request->payment_method)->payment_mode ?? null;
                    $pVoucher->payment_account_head_id = $request->payment_method; // Payment Mode
                    $pVoucher->amount = $loan->principal_amount; // Only disburse principal
                    $pVoucher->date = Carbon::parse($loan->disbursement_date)->format('Y-m-d');
                    $pVoucher->voucher_no_group_sl = $pNoGroupSl;
                    $pVoucher->voucher_no = $pNo;
                    $pVoucher->account_head_payee_depositor_id = $payeeId;
                    $pVoucher->user_id = auth()->id();
                    $pVoucher->save();

                    // Credit Cash/Bank
                    $this->createTransaction($pVoucher, $request->payment_method, TransactionType::$CREDIT, $loan->principal_amount, null);
                    
                    // Debit Customer
                    $this->createTransaction($pVoucher, $payeeId, TransactionType::$DEBIT, $loan->principal_amount, null);
                }

                DB::commit();
                return back()->with('success', 'Cash Loan Approved & Disbursed Successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        }
        return back()->with('error', 'Loan cannot be approved.');
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
