<?php

namespace App\Http\Controllers\Account;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SakibRahaman\DecimalToWords\DecimalToWords;
use App\Services\Reports\DailyTransactionReport;
use App\Support\ApplicationDate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function cashBankBook(Request $request)
    {

        $currentDate = Carbon::now();
        $firstDateOfMonth = $currentDate->startOfMonth()->format('d-m-Y');
        $lastDateOfMonth = $currentDate->endOfMonth()->format('d-m-Y');
        $accounts = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhereReportBranch('sister_concern_id'); })->where('payment_mode', '>', 0)->get();

        $selectAccount = null;
        $openingBalance = 0;
        $transactions = [];
        $summary = [];
        $start = date('Y-m-d', strtotime($request->start_date));
        $end = date('Y-m-d', strtotime($request->end_date));

        if ($request->payment_mode && $request->start_date && $request->end_date) {
            $query = Transaction::where('account_head_id', $request->payment_mode)
                ->whereIn('transaction_type', [TransactionType::$DEBIT, TransactionType::$CREDIT])
                ->whereBetween('date', [$start, $end]);

            $query->whereReportBranch('sister_concern_id');

            $transactions = $query->with('voucher')
                ->orderBy('voucher_no_group_sl')
                ->orderBy('date')
                ->get();

            $selectAccount = AccountHead::where('id', $request->payment_mode)->first();
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $totalReceivedQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$DEBIT)
                ->whereDate('date', '<=', $previousDay);

            $totalReceivedQuery->whereReportBranch('sister_concern_id');
            $totalReceived = $totalReceivedQuery->sum('amount');

            $totalPaymentQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$CREDIT)
                ->whereDate('date', '<=', $previousDay);

            $totalPaymentQuery->whereReportBranch('sister_concern_id');
            $totalPayment = $totalPaymentQuery->sum('amount');

            $openingBalance = $totalReceived - $totalPayment;

            // Generate summary by payment_purpose
            $purposeNames = PaymentPurpose::names();

            $summary = [];
            foreach ($transactions as $tx) {
                $purpose = $tx->payment_purpose;

                if (!$purpose) {
                    if ($tx->transaction_type == TransactionType::$DEBIT) {
                        $purpose = 'other_receipts';
                    } else {
                        $purpose = 'other_payments';
                    }
                }

                $purposeName = $purposeNames[$purpose] ?? 'Other / Unknown (' . $purpose . ')';

                if (!isset($summary[$purpose])) {
                    $summary[$purpose] = [
                        'id' => $purpose,
                        'name' => $purposeName,
                        'count' => 0,
                        'payment' => 0,
                        'receipt' => 0,
                    ];
                }

                $summary[$purpose]['count']++;

                if ($tx->transaction_type == TransactionType::$DEBIT) {
                    $summary[$purpose]['receipt'] += $tx->amount;
                } else if ($tx->transaction_type == TransactionType::$CREDIT) {
                    $summary[$purpose]['payment'] += $tx->amount;
                }
            }
        }
        $sister_concerns = SisterConcern::where('status', 1)->get();

        if ($request->has('search')) {
            return view(
                'accounts.reports.cash_bank_book_report',
                compact(
                    'firstDateOfMonth',
                    'lastDateOfMonth',
                    'accounts',
                    'selectAccount',
                    'transactions',
                    'openingBalance',
                    'sister_concerns',
                    'summary'
                )
            );
        }

        return view(
            'accounts.reports.cash_bank_book',
            compact(
                'firstDateOfMonth',
                'lastDateOfMonth',
                'accounts',
                'selectAccount',
                'transactions',
                'openingBalance',
                'sister_concerns',
                'summary'
            )
        );
    }

    public function cashBankTransactionDetails(Request $request)
    {

        $currentDate = Carbon::now();
        $firstDateOfMonth = $currentDate->startOfMonth()->format('d-m-Y');
        $lastDateOfMonth = $currentDate->endOfMonth()->format('d-m-Y');
        $accounts = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhereReportBranch('sister_concern_id'); })->where('payment_mode', '>', 0)->get();

        $selectAccount = null;
        $openingBalance = 0;
        $transactions = [];
        $start = date('Y-m-d', strtotime($request->start_date));
        $end = date('Y-m-d', strtotime($request->end_date));

        if ($request->payment_mode && $request->start_date && $request->end_date) {
            $query = Transaction::where('account_head_id', $request->payment_mode)
                ->whereIn('transaction_type', [TransactionType::$DEBIT, TransactionType::$CREDIT])
                ->whereBetween('date', [$start, $end]);

            $query->whereReportBranch('sister_concern_id');

            $transactions = $query->with('voucher')
                ->orderBy('voucher_no_group_sl')
                ->orderBy('date')
                ->get();

            $selectAccount = AccountHead::where('id', $request->payment_mode)->first();
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $totalReceivedQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$DEBIT)
                ->whereDate('date', '<=', $previousDay);

            $totalReceivedQuery->whereReportBranch('sister_concern_id');
            $totalReceived = $totalReceivedQuery->sum('amount');

            $totalPaymentQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$CREDIT)
                ->whereDate('date', '<=', $previousDay);

            $totalPaymentQuery->whereReportBranch('sister_concern_id');
            $totalPayment = $totalPaymentQuery->sum('amount');

            $openingBalance = $totalReceived - $totalPayment;

        }
        $sister_concerns = SisterConcern::where('status', 1)->get();

        if ($request->has('search')) {
            return view(
                'accounts.reports.cash_bank_transaction_details_report',
                compact(
                    'firstDateOfMonth',
                    'lastDateOfMonth',
                    'accounts',
                    'selectAccount',
                    'transactions',
                    'openingBalance',
                    'sister_concerns'
                )
            );
        }

        return view(
            'accounts.reports.cash_bank_transaction_details',
            compact(
                'firstDateOfMonth',
                'lastDateOfMonth',
                'accounts',
                'selectAccount',
                'transactions',
                'openingBalance',
                'sister_concerns'
            )
        );
    }

    public function receiptAndPayment(Request $request)
    {
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $query = Transaction::whereIn(
                'voucher_type',
                [
                    VoucherType::$PAYMENT_VOUCHER,
                    VoucherType::$COLLECTION_VOUCHER
                ]
            )
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                ->where('other_deduct', 0)
                ->where('except_edit', 0);

            $query->whereReportBranch('sister_concern_id');

            $logs = $query->orderBy('date')
                ->get();
        }

        if ($request->has('search')) {
            return view('accounts.reports.receipt_and_payment_report', compact('logs'));
        }

        return view('accounts.reports.receipt_and_payment', compact('logs'));
    }

    public function ledger(Request $request)
    {
        $startDate = date('Y-m-d', strtotime($request->start_date));
        $endDate = date('Y-m-d', strtotime($request->end_date));

        $accountHeadsList = AccountHead::where('status', 1)->orderBy('code')->get();
        $accountHeads = [];
        if ($request->start_date != '') {
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $query = AccountHead::where('status', 1);
            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }
            if ($request->account_head_id != '') {
                $query->where('id', $request->account_head_id);
            }
            $query->with([
                'openingTransactions' => function ($query) use ($previousDay, $request) {
                    $query->whereReportBranch('sister_concern_id');

                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,
                            SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                            SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id');
                },
                'transactions' => function ($query) use ($startDate, $endDate, $request) {
                    $query->whereReportBranch('sister_concern_id');

                    $query->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->orderBy('voucher_no_group_sl');
                },
            ]);

            $accountHeads = $query->orderBy('code')
                ->get();
        }

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        if ($request->has('search')) {
            return view('accounts.reports.ledger_report', compact(
                'accountHeads',
                'currentDate',
                'accountHeadsList'
            ));
        }

        return view('accounts.reports.ledger', compact(
            'accountHeads',
            'currentDate',
            'accountHeadsList'
        ));
    }

    public function trialBalance(Request $request)
    {
        $accountHeadsList = AccountHead::where('status', 1)->orderBy('code')->get();

        $accountHeads = [];

        $filterFinancialYear = null;
        if ($request->start_date != '' && $request->end_date != '') {
            $query = AccountHead::where('status', 1);

            if ($request->account_head != '') {
                $query->where('id', $request->account_head);
            }
            if ($request->account_group_id != '') {
                $group = AccountGroup::find($request->account_group_id);
                if ($group) {
                    $groupIds = $group->getAllChildIds();
                    $groupIds[] = $group->id;
                    $query->whereIn('account_group_id', $groupIds);
                }
            }

            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');
            $accountHeads = $query
                ->with([
                    'openingTransactions' => function ($query) use ($previousDay, $request) {

                        $query->whereReportBranch('sister_concern_id');

                        $query->whereDate('date', '<=', $previousDay)
                            ->select(DB::raw('account_head_id,
                        SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                        SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                            ->groupBy('account_head_id');
                    }
                ])
                ->with([
                    'transactions' => function ($query) use ($start, $end, $request) {
                        $query->whereReportBranch('sister_concern_id');

                        $query->whereBetween('date', [$start, $end])
                            ->select(DB::raw('account_head_id,
                         SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS debit,
                         SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS credit'))
                            ->groupBy('account_head_id');
                    }
                ])
                ->orderBy('code')
                ->get();
            // dd($accountHeads);    
        }

        $in_word = new DecimalToWords();

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }


        if ($request->has('search')) {
            return view('accounts.reports.trial_balance_report', compact(
                'currentDate',
                'accountHeads',
                'in_word',
                'accountHeadsList',
                'filterFinancialYear'
            ));
        }

        return view('accounts.reports.trial_balance', compact(
            'currentDate',
            'accountHeads',
            'in_word',
            'accountHeadsList',
            'filterFinancialYear'
        ));
    }

    public function incomeStatement(Request $request)
    {
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        // --- New 13 Sub-group structure for Income Statement ---
        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        if ($request->has('search')) {
            return view('accounts.reports.income_statement_t_format_report', compact(
                'currentDate',
                'operatingRevenue',
                'otherIncome',
                'costOfGoodsSold',
                'operatingExpenses',
                'financialExpenses',
                'taxExpenses'
            ));
        }

        return view('accounts.reports.income_statement', compact(
            'currentDate',
            'operatingRevenue',
            'otherIncome',
            'costOfGoodsSold',
            'operatingExpenses',
            'financialExpenses',
            'taxExpenses'
        ));
    }


    public function balanceSheet(Request $request)
    {

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $assets = AccountGroup::where('reserved_id', ReservedAccountGroup::ASSETS)
            ->with('accountGroups')
            ->get();
        $liabilities = AccountGroup::where('reserved_id', ReservedAccountGroup::LIABILITIES)
            ->with('accountGroups')
            ->get();
        $equities = AccountGroup::where('reserved_id', ReservedAccountGroup::EQUITY)
            ->with('accountGroups')
            ->get();

        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        if ($request->has('search')) {
            return view('accounts.reports.balance_sheet_t_format_report', compact(
                'assets',
                'liabilities',
                'equities',
                'currentDate',
                'operatingRevenue',
                'otherIncome',
                'costOfGoodsSold',
                'operatingExpenses',
                'financialExpenses',
                'taxExpenses'
            ));
        }

        return view('accounts.reports.balance_sheet_t_format', compact(
            'assets',
            'liabilities',
            'equities',
            'currentDate',
            'operatingRevenue',
            'otherIncome',
            'costOfGoodsSold',
            'operatingExpenses',
            'financialExpenses',
            'taxExpenses'
        ));
    }

    public function interCompanyLedger(Request $request)
    {
        $branches = SisterConcern::where('status', 1)
            ->when(\App\Support\ReportBranch::id() !== null, fn ($q) => $q->where('id', '!=', \App\Support\ReportBranch::id()))
            ->get();

        $transactions = collect();
        $openingBalance = 0;
        $selectedBranch = null;
        $receivableHead = null;
        $payableHead = null;

        if ($request->branch_id && $request->start_date && $request->end_date) {
            $selectedBranch = SisterConcern::find($request->branch_id);
            $start = date('Y-m-d', strtotime($request->start_date));
            $end = date('Y-m-d', strtotime($request->end_date));

            // The branch code or id is used to find the account heads.
            $receivableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_RECEIVABLE)
                ->where('name', 'like', 'Inter-company Receivable - ' . ($selectedBranch->code ?? sprintf('%03d', $selectedBranch->id)) . '%')
                ->whereNull('sister_concern_id')
                ->first();

            $payableHead = AccountHead::where('reserved_id', ReservedAccountHead::INTER_COMPANY_PAYABLE)
                ->where('name', 'like', 'Inter-company Payable - ' . ($selectedBranch->code ?? sprintf('%03d', $selectedBranch->id)) . '%')
                ->whereNull('sister_concern_id')
                ->first();

            $headIds = [];
            if ($receivableHead)
                $headIds[] = $receivableHead->id;
            if ($payableHead)
                $headIds[] = $payableHead->id;

            if (count($headIds) > 0) {
                // Get Opening Balance up to start_date
                $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($start)));

                $prevReceivableDebit = Transaction::where('account_head_id', $receivableHead->id ?? 0)
                    ->whereReportBranch('sister_concern_id')
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');
                $prevReceivableCredit = Transaction::where('account_head_id', $receivableHead->id ?? 0)
                    ->whereReportBranch('sister_concern_id')
                    ->where('transaction_type', TransactionType::$CREDIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');

                $prevPayableCredit = Transaction::where('account_head_id', $payableHead->id ?? 0)
                    ->whereReportBranch('sister_concern_id')
                    ->where('transaction_type', TransactionType::$CREDIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');
                $prevPayableDebit = Transaction::where('account_head_id', $payableHead->id ?? 0)
                    ->whereReportBranch('sister_concern_id')
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->whereDate('date', '<=', $previousDay)->sum('amount');

                // Net Receivable Opening Balance = (Rec Debit - Rec Credit) - (Pay Credit - Pay Debit)
                $openingBalance = ($prevReceivableDebit - $prevReceivableCredit) - ($prevPayableCredit - $prevPayableDebit);

                $transactions = Transaction::whereIn('account_head_id', $headIds)
                    ->whereReportBranch('sister_concern_id')
                    ->whereBetween('date', [$start, $end])
                    ->with('voucher')
                    ->orderBy('date')
                    ->orderBy('voucher_no_group_sl')
                    ->get();
            }
        }

        if ($request->has('search')) {
            return view('accounts.reports.inter_company_ledger_report', compact('branches', 'transactions', 'openingBalance', 'selectedBranch', 'receivableHead', 'payableHead'));
        }

        return view('accounts.reports.inter_company_ledger', compact('branches', 'transactions', 'openingBalance', 'selectedBranch', 'receivableHead', 'payableHead'));
    }

    private function dailyReportFilters(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d,d-m-Y'],
            'end_date' => ['nullable', 'date_format:Y-m-d,d-m-Y'],
            'branch_id' => ['nullable', 'integer', 'exists:sister_concerns,id'],
            'category' => ['nullable', Rule::in(array_merge(['all'], array_keys(DailyTransactionReport::SECTIONS)))],
            'account_head_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->format('Y-m-d') : ApplicationDate::today();
        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->format('Y-m-d') : ApplicationDate::today();
        if ($endDate < $startDate) {
            throw ValidationException::withMessages(['end_date' => 'End date must be on or after start date.']);
        }
        $activeBranch = auth()->user()->activeConcernId();
        $requestedBranch = $validated['branch_id'] ?? null;
        if (! \App\Support\ReportBranch::allowed()) {
            abort_if($requestedBranch !== null && (int) $activeBranch !== (int) $requestedBranch, 403);
        } elseif (! $request->has('report_branch_id') && $requestedBranch !== null) {
            $request->merge(['report_branch_id' => $requestedBranch]);
        }
        $branchId = \App\Support\ReportBranch::id($request);
        $category = $validated['category'] ?? 'all';
        return compact('startDate', 'endDate', 'branchId', 'category');
    }

    public function dailyTransactions(Request $request)
    {
        $filters = $this->dailyReportFilters($request);
        $sister_concerns = SisterConcern::orderBy('name')->get();
        $sectionNames = DailyTransactionReport::SECTIONS;
        $report = $request->has('search')
            ? app(DailyTransactionReport::class)->summary($filters['startDate'], $filters['endDate'], $filters['branchId'])
            : null;
        return view($request->has('search') ? 'accounts.reports.daily_transactions_report' : 'accounts.reports.daily_transactions',
            array_merge($filters, compact('sister_concerns', 'sectionNames', 'report')));
    }

    public function dailyTransactionsDetails(Request $request)
    {
        $filters = $this->dailyReportFilters($request);
        $request->validate(['account_head_id' => ['required', 'integer', 'min:1']]);
        $service = app(DailyTransactionReport::class);
        $query = $service->query($filters['startDate'], $filters['endDate'], $filters['branchId'])
            ->where('account_head_id', $request->integer('account_head_id'));
        // The same scope and ledger lines power summary and details.
        abort_unless((clone $query)->exists(), 404);
        $head = AccountHead::withTrashed()->with('supplier')->find($request->integer('account_head_id'));
        $totals = (clone $query)
            ->selectRaw('SUM(CASE WHEN transaction_type = 1 THEN CAST(amount AS DECIMAL(24,2)) ELSE 0 END) AS debit')
            ->selectRaw('SUM(CASE WHEN transaction_type = 2 THEN CAST(amount AS DECIMAL(24,2)) ELSE 0 END) AS credit')->first();
        $transactions = $query->with([
            'voucher' => fn ($q) => $q->withTrashed(),
            'voucher.transactions' => fn ($q) => $q->when($filters['branchId'] !== null, fn ($q) => $q->where('sister_concern_id', $filters['branchId'])),
            'voucher.transactions.accountHead' => fn ($q) => $q->withTrashed()->with('supplier'),
            'payeeDepositorHead' => fn ($q) => $q->withTrashed()->with('supplier'),
        ])->orderBy('date')->orderBy('id')->paginate(100)->withQueryString();
        $sister_concerns = SisterConcern::get()->keyBy('id');
        $purposeNames = PaymentPurpose::names();
        return view('accounts.reports.daily_transactions_details', array_merge($filters, compact('head', 'transactions', 'totals', 'sister_concerns', 'purposeNames')));
    }
}
