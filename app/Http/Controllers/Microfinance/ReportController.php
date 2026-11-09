<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSavingsAccount;
use App\Models\Microfinance\MfLoan;
use App\Models\Microfinance\MfSomiti;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function memberLedger(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_member_ledger'), 403, 'Unauthorized');
        $pageTitle = 'Member Ledger';
        $members   = MfMember::where('sister_concern_id', auth()->user()->activeConcernId())
                              ->where('status', 1)->orderBy('name')->get();

        $member  = null;
        $savings = [];
        $loans   = [];

        if ($request->member_id) {
            $member  = MfMember::where('sister_concern_id', auth()->user()->activeConcernId())
                                ->findOrFail($request->member_id);
            $savings = MfSavingsAccount::with('product', 'transactions')
                                       ->where('member_id', $member->id)->get();
            $loans   = MfLoan::with('product', 'installments')
                              ->where('member_id', $member->id)->get();
                              
            return view('microfinance.report.member_ledger_report', compact('pageTitle', 'members', 'member', 'savings', 'loans'));
        }

        return view('microfinance.report.member_ledger', compact('pageTitle', 'members', 'member', 'savings', 'loans'));
    }

    public function collectionSheet(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_collection_sheet'), 403, 'Unauthorized');
        $pageTitle = 'Collection Sheet Report';
        $scId      = auth()->user()->activeConcernId();
        $somitis   = MfSomiti::where('sister_concern_id', $scId)->where('status', 1)->get();
        $date      = $request->date ?? date('Y-m-d');
        $somitiId  = $request->somiti_id;

        $members = collect();
        if ($somitiId) {
            // Verify Somiti belongs to the active branch
            $somiti = MfSomiti::where('sister_concern_id', $scId)->findOrFail($somitiId);

            $members = MfMember::with([
                'loans' => fn($q) => $q->where('status', 3),
                'loans.installments' => fn($q) => $q->where('due_date', '<=', $date)->whereIn('status', [1, 3]),
                'savingsAccounts' => fn($q) => $q->where('status', 1),
                'savingsAccounts.product',
            ])->where('somiti_id', $somiti->id)->where('status', 1)->get();
            
            if ($request->has('search')) {
                return view('microfinance.report.collection_sheet_report', compact('pageTitle', 'somiti', 'date', 'members'));
            }
        }

        return view('microfinance.report.collection_sheet', compact('pageTitle', 'somitis', 'date', 'somitiId', 'members'));
    }

    public function loanPortfolio(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_loan_portfolio'), 403, 'Unauthorized');
        $pageTitle = 'Loan Portfolio';
        $scId      = auth()->user()->activeConcernId();

        $loans = MfLoan::with('member', 'product')
            ->where('sister_concern_id', $scId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->get();

        $summary = [
            'total_loans'      => $loans->count(),
            'total_disbursed'  => $loans->where('status', 3)->sum('disbursed_amount'),
            'active_loans'     => $loans->where('status', 3)->count(),
            'closed_loans'     => $loans->where('status', 4)->count(),
            'pending_loans'    => $loans->where('status', 1)->count(),
        ];

        if ($request->has('search')) {
            return view('microfinance.report.loan_portfolio_report', compact('pageTitle', 'loans', 'summary'));
        }

        return view('microfinance.report.loan_portfolio', compact('pageTitle', 'loans', 'summary'));
    }

    public function savingsPortfolio(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_savings_portfolio'), 403, 'Unauthorized');
        $pageTitle = 'Savings Portfolio';
        $scId      = auth()->user()->activeConcernId();

        $accounts = MfSavingsAccount::with('member', 'product')
            ->where('sister_concern_id', $scId)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->product_type, fn($q) => $q->whereHas('product', fn($p) => $p->where('product_type', $request->product_type)))
            ->get();

        $summary = [
            'total_accounts'   => $accounts->count(),
            'total_balance'    => $accounts->where('status', 1)->sum('current_balance'),
            'active_accounts'  => $accounts->where('status', 1)->count(),
            'pending_accounts' => $accounts->where('status', 0)->count(),
            'closed_accounts'  => $accounts->where('status', 3)->count(),
        ];
        
        if ($request->has('search')) {
            return view('microfinance.report.savings_portfolio_report', compact('pageTitle', 'accounts', 'summary'));
        }

        return view('microfinance.report.savings_portfolio', compact('pageTitle', 'accounts', 'summary'));
    }

    public function dueOverdue(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_due_overdue'), 403, 'Unauthorized');
        $pageTitle = 'Due / Overdue Report (NPL)';
        $scId      = auth()->user()->activeConcernId();
        
        $date = $request->date ?? date('Y-m-d');
        
        $installments = \App\Models\Microfinance\MfInstallment::with(['loan.member.somiti', 'loan.product'])
            ->whereHas('loan', fn($q) => $q->where('sister_concern_id', $scId)->where('status', 3))
            ->where('due_date', '<=', $date)
            ->whereIn('status', [1, 3]) // 1=Pending, 3=Partial
            ->orderBy('due_date', 'asc')
            ->get();
            
        $grouped = $installments->groupBy('mf_loan_id');
        
        if ($request->has('search')) {
            return view('microfinance.report.due_overdue_report', compact('pageTitle', 'grouped', 'date'));
        }
        return view('microfinance.report.due_overdue', compact('pageTitle', 'grouped', 'date'));
    }

    public function loanDisbursement(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_report_loan_disbursement'), 403, 'Unauthorized');
        $pageTitle = 'Loan Disbursement Report';
        $scId      = auth()->user()->activeConcernId();
        
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate   = $request->end_date ?? date('Y-m-d');
        
        $loans = \App\Models\Microfinance\MfLoan::with('member.somiti', 'product')
            ->where('sister_concern_id', $scId)
            ->where('status', 3) // Disbursed
            ->whereBetween('disbursement_date', [$startDate, $endDate])
            ->get();
            
        if ($request->has('search')) {
            return view('microfinance.report.loan_disbursement_report', compact('pageTitle', 'loans', 'startDate', 'endDate'));
        }
        return view('microfinance.report.loan_disbursement', compact('pageTitle', 'loans', 'startDate', 'endDate'));
    }
}
