<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiLoan\LoanAccount;
use App\Models\ShomriddhiLoan\LoanInstallment;
use App\Models\ShomriddhiLoan\LoanTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function loanLedger(Request $request)
    {
        $loans = LoanAccount::with('customer')->get();

        if ($request->search) {
            $selectedLoan = LoanAccount::with(['customer', 'installments', 'transactions' => function($q) {
                $q->orderBy('collection_date', 'asc');
            }])->findOrFail($request->loan_id);
            return view('shomriddhi_loan.reports.loan_ledger_report', compact('selectedLoan'));
        }

        return view('shomriddhi_loan.reports.loan_ledger', compact('loans'));
    }

    public function collectionSummary(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today()->endOfMonth();

        if ($request->search) {
            $transactions = LoanTransaction::with(['loanAccount.customer', 'collectedBy'])
                ->whereBetween('collection_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('collection_date', 'desc')
                ->get();

            $totalCollected = $transactions->sum('amount');

            return view('shomriddhi_loan.reports.collection_summary_report', compact('transactions', 'startDate', 'endDate', 'totalCollected'));
        }

        return view('shomriddhi_loan.reports.collection_summary', compact('startDate', 'endDate'));
    }

    public function overdueInstallments(Request $request)
    {
        if ($request->search) {
            // Find unpaid or partial installments whose due_date is before today
            $overdues = LoanInstallment::with('loanAccount.customer')
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->where('due_date', '<', date('Y-m-d'))
                ->orderBy('due_date', 'asc')
                ->get();

            return view('shomriddhi_loan.reports.overdue_installments_report', compact('overdues'));
        }
        
        return view('shomriddhi_loan.reports.overdue_installments');
    }
}
