<?php

namespace App\Http\Controllers\BranchManagement;

use App\Http\Controllers\Controller;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use App\Models\Hrm\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SakibRahaman\DecimalToWords\DecimalToWords;

class FinancialReportController extends Controller
{
    public function cashBankBook(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
        $currentDate = Carbon::now();
        $firstDateOfMonth = $currentDate->startOfMonth()->format('d-m-Y');
        $lastDateOfMonth = $currentDate->endOfMonth()->format('d-m-Y');
        $accounts = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhereReportBranch('sister_concern_id'); })->where('payment_mode', '>', 0)->get();
        
        $selectAccount = null;
        $openingBalance = 0;
        $transactions = [];
        $start = date('Y-m-d', strtotime($request->start_date));
        $end = date('Y-m-d', strtotime($request->end_date));

        if ($request->payment_mode && $request->start_date && $request->end_date) {
            $query = Transaction::where('account_head_id', $request->payment_mode)
                ->whereIn('transaction_type', [TransactionType::$DEBIT, TransactionType::$CREDIT])
                ->whereBetween('date', [$start, $end]);
                
            if ($request->sister_concern) {
                $query->where('sister_concern_id', $request->sister_concern);
            }

            $transactions = $query->with('voucher')
                ->orderBy('voucher_no_group_sl')
                ->orderBy('date')
                ->get();

            $selectAccount = AccountHead::where('id', $request->payment_mode)->first();
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($request->start_date)));

            $totalReceivedQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$DEBIT)
                ->whereDate('date', '<=', $previousDay);
            
            if ($request->sister_concern) {
                $totalReceivedQuery->where('sister_concern_id', $request->sister_concern);
            }
            $totalReceived = $totalReceivedQuery->sum('amount');

            $totalPaymentQuery = Transaction::where('account_head_id', $request->payment_mode)
                ->where('transaction_type', TransactionType::$CREDIT)
                ->whereDate('date', '<=', $previousDay);

            if ($request->sister_concern) {
                $totalPaymentQuery->where('sister_concern_id', $request->sister_concern);
            }
            $totalPayment = $totalPaymentQuery->sum('amount');

            $openingBalance = $totalReceived - $totalPayment;

        }
        $sister_concerns = SisterConcern::whereReportBranch('id')->get();

        if ($request->has('search')) {
            return view('branch_management.reports.financial.cash_bank_book_report',
                compact('firstDateOfMonth', 'lastDateOfMonth', 'accounts',
                    'selectAccount', 'transactions', 'openingBalance','sister_concerns'));
        }

        return view('branch_management.reports.financial.cash_bank_book',
            compact('firstDateOfMonth', 'lastDateOfMonth', 'accounts',
                'selectAccount', 'transactions', 'openingBalance','sister_concerns'));
    }

    public function receiptAndPayment(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
        $logs = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $query = Transaction::whereIn('voucher_type',
                [
                    VoucherType::$PAYMENT_VOUCHER,
                    VoucherType::$COLLECTION_VOUCHER
                ])
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])
                ->whereColumn('payment_account_head_id', '!=', 'account_head_id')
                ->where('other_deduct', 0)
                ->where('except_edit', 0); 

            if ($request->sister_concern) {
                $query->where('sister_concern_id', $request->sister_concern);
            } 

            $logs = $query->orderBy('date')
                ->get();
        }

        $sister_concerns = SisterConcern::whereReportBranch('id')->get();
        if ($request->has('search')) {
            return view('branch_management.reports.financial.receipt_and_payment_report', compact('logs', 'sister_concerns'));
        }

        return view('branch_management.reports.financial.receipt_and_payment', compact('logs', 'sister_concerns'));
    }

    public function ledger(Request $request)
    {
        $branchId = \App\Support\ReportBranch::id($request);
        $branch = $branchId === null ? new SisterConcern(['name' => 'All Branches']) : SisterConcern::findOrFail($branchId);
        $headsQuery = AccountHead::withTrashed()->where(fn ($q) => $q->whereNull('sister_concern_id')
            ->orWhereReportBranch('sister_concern_id', $branchId)
            ->orWhereHas('transactions', fn ($q) => $q->whereReportBranch('sister_concern_id', $branchId)));
        $accountHeadsList = (clone $headsQuery)->orderBy('code')->orderBy('id')->get();
        if (! $request->has('search') && ! $request->filled('account_head_id')) {
            return view('branch_management.reports.financial.ledger', compact('accountHeadsList'));
        }
        if (! $request->filled('account_head_id') && $request->filled('account_head')) $request->merge(['account_head_id' => $request->input('account_head')]);
        $filters = $request->validate(['account_head_id' => 'nullable|integer|min:1', 'start_date' => 'required|date_format:d-m-Y',
            'end_date' => 'required|date_format:d-m-Y|after_or_equal:start_date']);
        if (\App\Support\ReportBranch::allowed()) $filters['report_branch_id'] = $branchId ?? 'all';
        if (! empty($filters['account_head_id'])) {
            $headsQuery->whereKey($filters['account_head_id']);
            if (! (clone $headsQuery)->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['account_head_id' => 'Select a valid account head for this branch.']);
        }
        $start = Carbon::createFromFormat('d-m-Y', $filters['start_date'])->toDateString();
        $end = Carbon::createFromFormat('d-m-Y', $filters['end_date'])->toDateString();
        $statements = [];
        $branches = $branchId === null ? SisterConcern::orderBy('code')->orderBy('id')->get() : collect([$branch]);
        $heads = $headsQuery->orderBy('code')->orderBy('id')->get();
        $activity = \App\Models\Account\Transaction::whereIn('account_head_id', $heads->pluck('id'))
            ->whereReportBranch('sister_concern_id', $branchId)->where('date', '<=', $end)->whereIn('transaction_type', [1, 2])
            ->select('sister_concern_id', 'account_head_id')->distinct()->get()->groupBy('sister_concern_id');
        foreach ($branches as $statementBranch) {
            $activeHeads = array_fill_keys(($activity->get($statementBranch->id) ?? collect())->pluck('account_head_id')->all(), true);
            foreach ($heads as $head) {
                if (! isset($activeHeads[$head->id]) && ! ($branchId !== null && ! empty($filters['account_head_id']))) continue;
                $report = app(\App\Services\Reports\PartyLedgerReport::class)->generateHead($head->id, $statementBranch->id, $start, $end);
                if (($branchId !== null && ! empty($filters['account_head_id'])) || $report['rows'] || $report['opening'] !== 0) {
                    $statements[] = ['head' => $head, 'report' => $report, 'branch' => $statementBranch];
                }
            }
        }
        $pages = app(\App\Services\Reports\LedgerStatementPages::class)->make($statements);
        return view('branch_management.reports.financial.ledger_report', compact('branch', 'filters', 'statements', 'pages'));
    }

    public function branchLedger(Request $request)
    {
        $branchId = \App\Support\ReportBranch::id($request);
        if (! $request->has('search')) return view('branch_management.reports.financial.branch_ledger');
        $filters = $request->validate(['start_date' => 'required|date_format:d-m-Y', 'end_date' => 'required|date_format:d-m-Y|after_or_equal:start_date']);
        if (\App\Support\ReportBranch::allowed()) $filters['report_branch_id'] = $branchId ?? 'all';
        $start = Carbon::createFromFormat('d-m-Y', $filters['start_date'])->toDateString();
        $end = Carbon::createFromFormat('d-m-Y', $filters['end_date'])->toDateString();
        $result = app(\App\Services\Reports\BranchTransactionLedgerReport::class)->generate($branchId, $start, $end);
        $pages = app(\App\Services\Reports\LedgerStatementPages::class)->make($result['statements']);
        return view('branch_management.reports.financial.branch_ledger_report', compact('filters', 'result', 'pages'));
    }

    public function trialBalance(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
        $accountHeadsList = AccountHead::where('status',1)->orderBy('code')->get();
        
        $accountHeads = [];

        $filterFinancialYear = null;
        if ($request->start_date != '' && $request->end_date != '') {
            $query = AccountHead::where('status',1);

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
                ->with(['openingTransactions' => function ($query) use ($previousDay,$request) {
                    
                    if ($request->sister_concern) {
                        $query->where('sister_concern_id', $request->sister_concern);
                    }

                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,transaction_type,
                        SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                        SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id', 'transaction_type');
                }])
                ->with(['transactions' => function ($query) use ($start, $end,$request) {
                    if ($request->sister_concern) {
                        $query->where('sister_concern_id', $request->sister_concern);
                    }

                    $query->whereBetween('date', [$start, $end])
                        ->select(DB::raw('account_head_id,
                         SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS debit,
                         SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS credit'))
                        ->groupBy('account_head_id');
                }])
                ->orderBy('code')
                ->get();
        }

        $in_word = new DecimalToWords();

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $sister_concerns = SisterConcern::whereReportBranch('id')->get();
        if ($request->has('search') || $request->has('account_group_id')) {
            return view('branch_management.reports.financial.trial_balance_report', compact('currentDate', 'accountHeads', 'in_word',
                'accountHeadsList', 'filterFinancialYear', 'sister_concerns'));
        }

        return view('branch_management.reports.financial.trial_balance', compact('currentDate', 'accountHeads', 'in_word',
            'accountHeadsList', 'filterFinancialYear', 'sister_concerns'));
    }

    public function incomeStatement(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        $sister_concerns = SisterConcern::whereReportBranch('id')->get();
        if ($request->has('search')) {
            if (empty($request->sister_concern)) {
                return view('branch_management.reports.financial.income_statement_summary', compact('currentDate',
                    'operatingRevenue', 'otherIncome', 'costOfGoodsSold',
                    'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
            } else {
                return view('branch_management.reports.financial.income_statement_t_format_report', compact('currentDate',
                'operatingRevenue', 'otherIncome', 'costOfGoodsSold',
                'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
            }
        }
        return view('branch_management.reports.financial.income_statement', compact('currentDate',
            'operatingRevenue', 'otherIncome', 'costOfGoodsSold',
            'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
    }
    public function dailyIncomeStatement(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $operatingRevenue = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_REVENUE)->first();
        $otherIncome = AccountGroup::where('reserved_id', ReservedAccountGroup::OTHER_INCOME)->first();
        $costOfGoodsSold = AccountGroup::where('reserved_id', ReservedAccountGroup::COST_OF_GOODS_SOLD)->first();
        $operatingExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $financialExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::FINANCIAL_EXPENSES)->first();
        $taxExpenses = AccountGroup::where('reserved_id', ReservedAccountGroup::TAX_EXPENSES)->first();

        $sister_concerns = SisterConcern::whereReportBranch('id')->get();
        
        if ($request->has('search')) {
            $branchId = $request->sister_concern;
            $branch = $branchId ? SisterConcern::find($branchId) : null;
            
            $data = \App\Services\BranchManagement\FinancialService::calculateDailyIncomeStatement(
                request('start_date'), 
                request('end_date'), 
                $branchId
            );

            // Merge data with what view expects
            $data = array_merge($data, compact(
                'currentDate', 'operatingRevenue', 'otherIncome', 'costOfGoodsSold',
                'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns', 'branch'
            ));

            return view('branch_management.reports.financial.daily_income_statement_report', $data);
        }
        
        return view('branch_management.reports.financial.daily_income_statement', compact('currentDate',
            'operatingRevenue', 'otherIncome', 'costOfGoodsSold',
            'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
    }

    public function balanceSheet(Request $request)
    {
        $request->merge(['sister_concern' => \App\Support\ReportBranch::id()]);
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

        $sister_concerns = SisterConcern::whereReportBranch('id')->get();
        if ($request->has('search')) {
            return view('branch_management.reports.financial.balance_sheet_t_format_report', compact('assets', 'liabilities',
            'equities', 'currentDate', 'operatingRevenue', 'otherIncome', 'costOfGoodsSold', 'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
        }
        return view('branch_management.reports.financial.balance_sheet_t_format', compact('assets', 'liabilities',
            'equities', 'currentDate', 'operatingRevenue', 'otherIncome', 'costOfGoodsSold', 'operatingExpenses', 'financialExpenses', 'taxExpenses', 'sister_concerns'));
    }
}
