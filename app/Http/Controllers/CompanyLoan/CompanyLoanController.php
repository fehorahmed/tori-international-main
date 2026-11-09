<?php

namespace App\Http\Controllers\CompanyLoan;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\CompanyLoan\CompanyLoan;
use App\Models\CompanyLoan\CompanyLoanInstallment;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompanyLoanController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_company_loan'), 403, 'Unauthorized');
        return view('company_loan.loan.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_company_loan'), 403, 'Unauthorized');
        $query = CompanyLoan::with('lender', 'borrower')
            ->where(function($q) {
                $q->where('lender_concern_id', auth()->user()->activeConcernId())
                  ->orWhere('borrower_concern_id', auth()->user()->activeConcernId());
            });

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (CompanyLoan $loan) {
                $btn = ' <a href="' . route('company_loan.show', ['loan' => $loan->id]) . '" class="btn btn-primary btn-sm"><i class="fa fa-info-circle"></i></a>';
                
                if ($loan->status == 1 && $loan->borrower_concern_id == auth()->user()->activeConcernId()) {
                    $btn .= ' <a href="' . route('company_loan.edit', ['loan' => $loan->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>';
                }

                if ($loan->status == 1 && $loan->lender_concern_id == auth()->user()->activeConcernId()) {
                    $btn .= ' <a href="' . route('company_loan.approval_page', ['loan' => $loan->id]) . '" class="btn btn-success btn-sm">Approve</a>';
                }
                
                return $btn;
            })
            ->addColumn('lender_name', function (CompanyLoan $loan) {
                return $loan->lender->name ?? '';
            })
            ->addColumn('borrower_name', function (CompanyLoan $loan) {
                return $loan->borrower->name ?? '';
            })
            ->addColumn('status_text', function (CompanyLoan $loan) {
                $status = [
                    1 => '<span class="badge badge-warning">Pending</span>',
                    2 => '<span class="badge badge-success">Approved</span>',
                    3 => '<span class="badge badge-danger">Rejected</span>',
                    4 => '<span class="badge badge-info">Completed</span>',
                ];
                return $status[$loan->status] ?? '';
            })
            ->rawColumns(['action', 'status_text'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_create'), 403, 'Unauthorized');
        $sisterConcerns = SisterConcern::where('id', '!=', auth()->user()->activeConcernId())->get();
        // Fetch Cash/Bank accounts for the current logged-in concern (Borrower)
        $receivingAccounts = AccountHead::whereIn('payment_mode', [1,2,3])
            ->get();
        
        return view('company_loan.loan.create', compact('sisterConcerns', 'receivingAccounts'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_create'), 403, 'Unauthorized');
        $request->validate([
            'lender_concern_id' => 'required|exists:sister_concerns,id',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric',
            'duration' => 'required|integer',
            'loan_type' => 'required',
            'calculation_method' => 'required',
            'start_date' => 'required',
            'borrower_account_head_id' => 'required|exists:account_heads,id',
        ]);

        CompanyLoan::create([
            'borrower_concern_id' => auth()->user()->activeConcernId(),
            'lender_concern_id' => $request->lender_concern_id,
            'amount' => $request->amount,
            'interest_rate' => $request->interest_rate,
            'duration' => $request->duration,
            'loan_type' => $request->loan_type,
            'calculation_method' => $request->calculation_method,
            'start_date' => Carbon::parse($request->start_date)->format('Y-m-d'),
            'borrower_account_head_id' => $request->borrower_account_head_id,
            'notes' => $request->notes,
            'status' => 1, // Pending
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('company_loan.index')->with('success', 'Loan request submitted successfully.');
    }

    public function edit(CompanyLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_edit'), 403, 'Unauthorized');
        if ($loan->status != 1 || $loan->borrower_concern_id != auth()->user()->activeConcernId()) {
            return redirect()->back()->with('error', 'Only pending loans can be edited by the borrower.');
        }

        $sisterConcerns = SisterConcern::where('id', '!=', auth()->user()->activeConcernId())->get();
        // Fetch Cash/Bank accounts for the current logged-in concern (Borrower)
        $receivingAccounts = AccountHead::whereIn('payment_mode', [1,2,3])
            ->get();

        return view('company_loan.loan.edit', compact('loan', 'sisterConcerns', 'receivingAccounts'));
    }

    public function update(CompanyLoan $loan, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_edit'), 403, 'Unauthorized');
        if ($loan->status != 1 || $loan->borrower_concern_id != auth()->user()->activeConcernId()) {
            return redirect()->back()->with('error', 'Unauthorized or cannot edit approved loan.');
        }

        $request->validate([
            'lender_concern_id' => 'required|exists:sister_concerns,id',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'loan_type' => 'required|in:Daily,Weekly,Monthly,Yearly',
            'duration' => 'required|integer|min:1',
            'calculation_method' => 'required|in:Flat,Reducing',
            'start_date' => 'required|date',
        ]);

        $data = $request->all();
        $data['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
        $loan->update($data);

        return redirect()->route('company_loan.index')->with('success', 'Loan request updated successfully.');
    }

    public function show(CompanyLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('manage_company_loan'), 403, 'Unauthorized');
        $loan->load('lender', 'borrower', 'installments');
        $paymentAccountHeads = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })
            ->where('payment_mode', '>', 0)
            ->orderBy('name')
            ->get();
        return view('company_loan.loan.show', compact('loan', 'paymentAccountHeads'));
    }

    public function approvalPage(CompanyLoan $loan)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_approve'), 403, 'Unauthorized');
        if ($loan->status != 1 || $loan->lender_concern_id != auth()->user()->activeConcernId()) {
            abort(403, 'Unauthorized or loan already processed.');
        }

        $loan->load('lender', 'borrower');
        
        // Fetch Cash/Bank accounts for the current logged-in concern (Lender)
        $disbursementAccounts = AccountHead::whereIn('payment_mode', [1,2,3])
            ->get();

        // Generate Preview Installments (Memory only, not saving)
        $previewInstallments = $this->calculatePreview($loan);

        return view('company_loan.loan.approve', compact('loan', 'previewInstallments', 'disbursementAccounts'));
    }

    /** Returns number of installment periods per year for a given loan type. */
    private function getPeriodsPerYear(string $type): int
    {
        return match($type) {
            'Daily'   => 365,
            'Weekly'  => 52,
            'Monthly' => 12,
            default   => 1, // Yearly
        };
    }

    private function calculatePreview(CompanyLoan $loan): array
    {
        $installments      = [];
        $amount            = $loan->amount;
        $rate              = $loan->interest_rate;
        $totalInstallments = $loan->duration;
        $method            = $loan->calculation_method;
        $type              = $loan->loan_type;
        $periodsPerYear    = $this->getPeriodsPerYear($type);

        if ($method == 'Flat') {
            $totalInterest           = ($amount * $rate * ($totalInstallments / $periodsPerYear)) / 100;
            $principalPerInstallment = $amount / $totalInstallments;
            $interestPerInstallment  = $totalInterest / $totalInstallments;
            $installmentTotal        = $principalPerInstallment + $interestPerInstallment;

            for ($i = 1; $i <= $totalInstallments; $i++) {
                $installments[] = (object)[
                    'installment_no'   => $i,
                    'due_date'         => $this->calculateNextDate($loan->start_date, $i, $type),
                    'total_amount'     => $installmentTotal,
                    'principal_amount' => $principalPerInstallment,
                    'interest_amount'  => $interestPerInstallment,
                ];
            }
        } else {
            // Reducing Balance (EMI)
            $periodicRate = ($rate / 100) / $periodsPerYear;
            $emi = $periodicRate > 0
                ? ($amount * $periodicRate * pow(1 + $periodicRate, $totalInstallments)) / (pow(1 + $periodicRate, $totalInstallments) - 1)
                : $amount / $totalInstallments;

            $remainingPrincipal = $amount;
            for ($i = 1; $i <= $totalInstallments; $i++) {
                $interest  = $remainingPrincipal * $periodicRate;
                $principal = $emi - $interest;
                $installments[] = (object)[
                    'installment_no'   => $i,
                    'due_date'         => $this->calculateNextDate($loan->start_date, $i, $type),
                    'total_amount'     => $emi,
                    'principal_amount' => $principal,
                    'interest_amount'  => $interest,
                ];
                $remainingPrincipal -= $principal;
            }
        }
        return $installments;
    }

    public function approve(CompanyLoan $loan, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_approve'), 403, 'Unauthorized');
        if ($loan->status != 1) {
            return redirect()->back()->with('error', 'Only pending loans can be approved.');
        }

        $request->validate([
            'lender_account_head_id' => 'required|exists:account_heads,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Generate Installments
            $this->generateInstallments($loan);

            // 2. Accounting Entries & Dynamic Heads
            $this->handleAccountingOnApproval($loan);

            $loan->status = 2; // Approved
            $loan->lender_account_head_id = $request->lender_account_head_id;
            $loan->approved_by = auth()->id();
            $loan->approved_at = now();
            $loan->save();

            DB::commit();
            return redirect()->route('company_loan.show', ['loan' => $loan->id])->with('success', 'Loan approved and installments generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reuses calculatePreview() and inserts all installments in ONE bulk query.
     * Previously ran N separate INSERT queries — now runs just 1.
     */
    private function generateInstallments(CompanyLoan $loan): void
    {
        $preview = $this->calculatePreview($loan);
        $now     = now()->toDateTimeString();

        $rows = array_map(fn($item) => [
            'company_loan_id'  => $loan->id,
            'installment_no'   => $item->installment_no,
            'due_date'         => $item->due_date,
            'total_amount'     => $item->total_amount,
            'principal_amount' => $item->principal_amount,
            'interest_amount'  => $item->interest_amount,
            'paid_amount'      => 0,
            'status'           => 1,
            'created_at'       => $now,
            'updated_at'       => $now,
        ], $preview);

        // Single bulk INSERT instead of N individual queries
        CompanyLoanInstallment::insert($rows);
    }

    private function calculateNextDate($startDate, $index, $type)
    {
        $date = Carbon::parse($startDate);
        if ($type == 'Daily') return $date->addDays($index)->format('Y-m-d');
        if ($type == 'Weekly') return $date->addWeeks($index)->format('Y-m-d');
        if ($type == 'Monthly') return $date->addMonths($index)->format('Y-m-d');
        if ($type == 'Yearly') return $date->addYears($index)->format('Y-m-d');
        return $startDate;
    }

    private function handleAccountingOnApproval(CompanyLoan $loan)
    {
        $borrowerCode = $loan->borrower->code ?? sprintf('%03d', $loan->borrower_concern_id);
        $lenderCode = $loan->lender->code ?? sprintf('%03d', $loan->lender_concern_id);

        // Lender Side (Dynamic Head Creation)
        $lenderLoanHead = $this->getOrCreateDynamicHead(
            $loan->lender_concern_id,
            ReservedAccountHead::COMPANY_LOAN_RECEIVABLE,
            "Loan Receivable - " . $borrowerCode . " (" . $loan->borrower->name . ")",
            "Loan Receivable - " . $borrowerCode,
            ReservedAccountGroup::ACCOUNTS_RECEIVABLE
        );

        // Borrower Side
        $borrowerLoanHead = $this->getOrCreateDynamicHead(
            $loan->borrower_concern_id,
            ReservedAccountHead::COMPANY_LOAN_PAYABLE,
            "Loan Payable - " . $lenderCode . " (" . $loan->lender->name . ")",
            "Loan Payable - " . $lenderCode,
            ReservedAccountGroup::SHORT_TERM_LOAN
        );
        // Disbursement Voucher (Admin/System level)
        // This usually happens across sister concerns
        // We will create two vouchers, one for each concern
        
        // Disbursement Voucher (Admin/System level)
        // Lender Side: Debit Receivable (Increase Asset), Credit Cash/Bank (Decrease Asset)
        $this->createVoucherWithTransaction(
            $loan->lender_concern_id,
            $lenderLoanHead->id,
            $loan->amount,
            TransactionType::$DEBIT, // Debiting Receivable
            "Loan Disbursement to " . $loan->borrower->name,
            $loan->id,
            $loan->lender_account_head_id // Counter (Cash) will be Credited
        );

        // Borrower Side: Debit Cash/Bank (Increase Asset), Credit Payable (Increase Liability)
        $this->createVoucherWithTransaction(
            $loan->borrower_concern_id,
            $borrowerLoanHead->id,
            $loan->amount,
            TransactionType::$CREDIT, // Crediting Payable
            "Loan Receipt from " . $loan->lender->name,
            $loan->id,
            $loan->borrower_account_head_id // Counter (Cash) will be Debited
        );
    }

    private function getOrCreateDynamicHead($sisterConcernId, $reservedId, $expectedName, $searchPrefix, $reservedGroupId)
    {
        // First find the group ID based on sister_concern_id and reservedGroupId
        $group = AccountGroup::where('reserved_id', $reservedGroupId)
            ->first();

        if (!$group) {
            throw new \Exception("Required Account Group (Reserved ID: $reservedGroupId) not found for Sister Concern ID: $sisterConcernId. Please run the AccountGroupSeeder.");
        }

        $head = AccountHead::where('reserved_id', $reservedId)
            ->where('name', 'like', $searchPrefix . '%')
            ->first();

        if ($head) {
            // If the company name was updated, dynamically update the account head name to match!
            if ($head->name !== $expectedName) {
                $head->name = $expectedName;
                $head->save();
            }
        } else {
            $topReservedId = $group->top_reserved_id;

            $maxCodeInTopParent = AccountHead::where('code', 'like', $topReservedId . '%')
                ->max('code');

            if ($maxCodeInTopParent) {
                $code = $maxCodeInTopParent + 1;
            } else {
                $code = $topReservedId . '001';
            }

            $nature = in_array($topReservedId, [1, 5]) ? 'Debit' : 'Credit';

            $head = AccountHead::create([
                'sister_concern_id' => null,
                'reserved_id' => $reservedId,
                'account_group_id' => $group->id,
                'name' => $expectedName,
                'code' => $code,
                'nature' => $nature,
                'is_system' => true,
                'status' => 1,
            ]);
        }
        return $head;
    }

    /** Generates the next sequential JV voucher number for a given sister concern. */
    private function generateVoucherNo(int $sisterConcernId): array
    {
        $prefix    = 'JV-';
        $maxNumber = DB::table('vouchers')
            ->where('sister_concern_id', $sisterConcernId)
            ->where('voucher_no', 'LIKE', "{$prefix}%")
            ->select(DB::raw("MAX(CAST(SUBSTRING(voucher_no, LENGTH('{$prefix}') + 1) AS UNSIGNED)) as max_number"))
            ->value('max_number');

        $newNumber = $maxNumber ? $maxNumber + 1 : 1;
        $voucherNo = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        $groupSl   = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        return [$voucherNo, $groupSl];
    }

    private function createVoucherWithTransaction($sisterConcernId, $headId, $amount, $type, $notes, $loanId, $counterHeadId = null)
    {
        $voucherType           = VoucherType::$JOURNAL_VOUCHER;
        [$voucherNo, $groupSl] = $this->generateVoucherNo($sisterConcernId);

        $voucher = Voucher::create([
            'sister_concern_id'   => $sisterConcernId,
            'voucher_type'        => $voucherType,
            'amount'              => $amount,
            'date'                => now()->format('Y-m-d'),
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $groupSl,
            'notes'               => $notes,
            'user_id'             => auth()->id(),
            'service_id'          => null,
            'company_loan_id'     => $loanId,
        ]);

        $shared = [
            'sister_concern_id'   => $sisterConcernId,
            'voucher_id'          => $voucher->id,
            'company_loan_id'     => $loanId,
            'voucher_type'        => $voucherType,
            'date'                => now()->format('Y-m-d'),
            'user_id'             => auth()->id(),
            'notes'               => $notes,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $groupSl,
        ];

        // 1. Loan Head Transaction
        Transaction::create(array_merge($shared, [
            'account_head_id'  => $headId,
            'transaction_type' => $type,
            'amount'           => $amount,
        ]));

        // 2. Counter entry (Cash/Bank)
        if (!$counterHeadId) {
            $counterHeadId = AccountHead::where('payment_mode', 2)->value('id');
        }
        if (!$counterHeadId) {
            throw new \Exception("Cash/Bank account head not found for Sister Concern ID: " . $sisterConcernId);
        }

        Transaction::create(array_merge($shared, [
            'account_head_id'  => $counterHeadId,
            'transaction_type' => $type == TransactionType::$DEBIT ? TransactionType::$CREDIT : TransactionType::$DEBIT,
            'amount'           => $amount,
        ]));
    }

    public function payInstallment(CompanyLoanInstallment $installment, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_payment'), 403, 'Unauthorized');
        if ($installment->status == 2) {
            return redirect()->back()->with('error', 'Installment already paid.');
        }

        $request->validate([
            'payment_date' => 'required|date',
            'payment_account_head_id' => 'required|exists:account_heads,id',
        ]);

        $paymentDate = Carbon::parse($request->payment_date)->format('Y-m-d');
        $borrowerAccountHeadId = $request->payment_account_head_id;

        DB::beginTransaction();
        try {
            $loan = $installment->loan;

            $borrowerCode = $loan->borrower->code ?? sprintf('%03d', $loan->borrower_concern_id);
            $lenderCode = $loan->lender->code ?? sprintf('%03d', $loan->lender_concern_id);

            // Borrower Side: Debit Payable & Interest Expense, Credit Cash
            $borrowerLoanHead = $this->getOrCreateDynamicHead(
                $loan->borrower_concern_id,
                ReservedAccountHead::COMPANY_LOAN_PAYABLE,
                "Loan Payable - " . $lenderCode . " (" . $loan->lender->name . ")",
                "Loan Payable - " . $lenderCode,
                ReservedAccountGroup::SHORT_TERM_LOAN
            );

            // Create Repayment Voucher for Borrower
            $this->createRepaymentVoucher(
                $loan->borrower_concern_id,
                $borrowerLoanHead->id,
                $installment->principal_amount,
                $installment->interest_amount,
                TransactionType::$DEBIT, // Debiting liability
                "Loan Repayment to " . $loan->lender->name . " (Inst. #" . $installment->installment_no . ")",
                $loan->id,
                ReservedAccountHead::COMPANY_LOAN_INTEREST_EXPENSE,
                ReservedAccountGroup::FINANCIAL_EXPENSES,
                $borrowerAccountHeadId,
                $paymentDate
            );

            // Lender Side: Debit Cash, Credit Receivable & Interest Income
            $lenderLoanHead = $this->getOrCreateDynamicHead(
                $loan->lender_concern_id,
                ReservedAccountHead::COMPANY_LOAN_RECEIVABLE,
                "Loan Receivable - " . $borrowerCode . " (" . $loan->borrower->name . ")",
                "Loan Receivable - " . $borrowerCode,
                ReservedAccountGroup::ACCOUNTS_RECEIVABLE
            );

            $lenderAccountHeadId = $loan->lender_account_head_id;

            if (!$lenderAccountHeadId) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Lender disbursement account not found. Please contact the lender to update the loan.');
            }

            $this->createRepaymentVoucher(
                $loan->lender_concern_id,
                $lenderLoanHead->id,
                $installment->principal_amount,
                $installment->interest_amount,
                TransactionType::$CREDIT, // Crediting asset
                "Loan Repayment from " . $loan->borrower->name . " (Inst. #" . $installment->installment_no . ")",
                $loan->id,
                ReservedAccountHead::COMPANY_LOAN_INTEREST_INCOME,
                ReservedAccountGroup::REVENUE,
                $lenderAccountHeadId,
                $paymentDate
            );

            $installment->status = 2; // Paid
            $installment->payment_date = $paymentDate;
            $installment->paid_amount = $installment->total_amount;
            $installment->save();

            // Check if all installments paid
            if ($loan->installments()->where('status', 1)->count() == 0) {
                $loan->status = 4; // Completed
                $loan->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Installment paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function createRepaymentVoucher($sisterConcernId, $loanHeadId, $principal, $interest, $type, $notes, $loanId, $interestReservedId, $interestGroupId, $counterHeadId, $date)
    {
        $voucherType           = VoucherType::$JOURNAL_VOUCHER;
        [$voucherNo, $groupSl] = $this->generateVoucherNo($sisterConcernId);
        $totalAmount           = $principal + $interest;

        $voucher = Voucher::create([
            'sister_concern_id'   => $sisterConcernId,
            'voucher_type'        => $voucherType,
            'amount'              => $totalAmount,
            'date'                => $date,
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $groupSl,
            'notes'               => $notes,
            'user_id'             => auth()->id(),
            'company_loan_id'     => $loanId,
        ]);

        $shared = [
            'sister_concern_id'   => $sisterConcernId,
            'voucher_id'          => $voucher->id,
            'company_loan_id'     => $loanId,
            'voucher_type'        => $voucherType,
            'date'                => $date,
            'user_id'             => auth()->id(),
            'voucher_no'          => $voucherNo,
            'voucher_no_group_sl' => $groupSl,
        ];

        // Principal Transaction
        Transaction::create(array_merge($shared, [
            'account_head_id'  => $loanHeadId,
            'transaction_type' => $type,
            'amount'           => $principal,
        ]));

        // Interest Transaction
        $interestLabel = $interestReservedId == ReservedAccountHead::COMPANY_LOAN_INTEREST_INCOME
            ? "Loan Interest Income"
            : "Loan Interest Expense";

        $interestHead = $this->getOrCreateDynamicHead(
            $sisterConcernId, $interestReservedId,
            $interestLabel, $interestLabel, $interestGroupId
        );

        Transaction::create(array_merge($shared, [
            'account_head_id'  => $interestHead->id,
            'transaction_type' => $type,
            'amount'           => $interest,
        ]));

        // Cash/Bank Counter Transaction
        Transaction::create(array_merge($shared, [
            'account_head_id'  => $counterHeadId,
            'transaction_type' => $type == TransactionType::$DEBIT ? TransactionType::$CREDIT : TransactionType::$DEBIT,
            'amount'           => $totalAmount,
        ]));
    }
}
