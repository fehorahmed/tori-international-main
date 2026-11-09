<?php

namespace App\Http\Controllers\CompanyLoan;

use App\Http\Controllers\Controller;
use App\Models\CompanyLoan\CompanyLoan;
use App\Models\CompanyLoan\CompanyLoanInstallment;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanReportController extends Controller
{
    public function loanStatement(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_report_statement'), 403, 'Unauthorized');
        $loans = CompanyLoan::where(function($q) {
            $q->where('lender_concern_id', auth()->user()->activeConcernId())
              ->orWhere('borrower_concern_id', auth()->user()->activeConcernId());
        })->get();

        $selectedLoan = null;
        $transactions = [];

        if ($request->loan_id) {
            $selectedLoan = CompanyLoan::where(function($q) {
                $q->where('lender_concern_id', auth()->user()->activeConcernId())
                  ->orWhere('borrower_concern_id', auth()->user()->activeConcernId());
            })->with('lender', 'borrower')->find($request->loan_id);

            if (!$selectedLoan) {
                return redirect()->route('company_loan.reports.statement')->with('error', 'Unauthorized access or loan not found.');
            }

            $isLender = (auth()->user()->activeConcernId() == $selectedLoan->lender_concern_id);
            
            // Filter by the specific Principal Account Head
            $principalHeadId = $isLender ? $selectedLoan->lender_account_head_id : $selectedLoan->borrower_account_head_id;

            $transactions = Transaction::where('company_loan_id', $request->loan_id)
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('account_head_id', $principalHeadId)
                ->with('voucher', 'accountHead')
                ->orderBy('date')
                ->orderBy('id')
                ->get();
        }

        return view('company_loan.reports.loan_statement', compact('loans', 'selectedLoan', 'transactions'));
    }

    public function loanReceivableSummary(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_report_receivable'), 403, 'Unauthorized');
        $sisterConcerns = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        $scId = auth()->user()->activeConcernId();
        $summary = [];

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');

            $summary = CompanyLoan::with('lender', 'borrower', 'installments')
                ->where('lender_concern_id', $scId)
                ->whereBetween('start_date', [$start, $end])
                ->get();
        }

        return view('company_loan.reports.loan_receivable_summary', compact('sisterConcerns', 'summary'));
    }

    public function loanPayableSummary(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_report_payable'), 403, 'Unauthorized');
        $sisterConcerns = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        $scId = auth()->user()->activeConcernId();
        $summary = [];

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->format('Y-m-d');
            $end = Carbon::parse($request->end_date)->format('Y-m-d');

            $summary = CompanyLoan::with('lender', 'borrower', 'installments')
                ->where('borrower_concern_id', $scId)
                ->whereBetween('start_date', [$start, $end])
                ->get();
        }

        return view('company_loan.reports.loan_payable_summary', compact('sisterConcerns', 'summary'));
    }

    public function dueInstallments(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('company_loan_report_due_installments'), 403, 'Unauthorized');
        $query = CompanyLoanInstallment::with('loan.lender', 'loan.borrower')
            ->where('status', 1) // Pending
            ->where('due_date', '<=', now()->format('Y-m-d'));

        $sisterConcerns = SisterConcern::where('id', auth()->user()->activeConcernId())->get();
        
        $query->whereHas('loan', function($q) {
            $q->where('lender_concern_id', auth()->user()->activeConcernId())
              ->orWhere('borrower_concern_id', auth()->user()->activeConcernId());
        });

        $installments = $query->orderBy('due_date')->get();

        return view('company_loan.reports.due_installments', compact('installments', 'sisterConcerns'));
    }
}
