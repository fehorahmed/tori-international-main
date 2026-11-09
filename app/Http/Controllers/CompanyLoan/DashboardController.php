<?php

namespace App\Http\Controllers\CompanyLoan;

use App\Http\Controllers\Controller;
use App\Models\CompanyLoan\CompanyLoan;
use App\Models\CompanyLoan\CompanyLoanInstallment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $concernId = auth()->user()->activeConcernId();
        
        $loansGiven = CompanyLoan::where('lender_concern_id', $concernId)->count();
        $loansTaken = CompanyLoan::where('borrower_concern_id', $concernId)->count();
        
        $pendingRequests = CompanyLoan::where(function($q) use ($concernId) {
            $q->where('lender_concern_id', $concernId)
              ->orWhere('borrower_concern_id', $concernId);
        })->where('status', 1)->count();
        
        // Outstanding Receivable (Loans Given where status is Approved)
        $outstandingReceivable = CompanyLoanInstallment::whereHas('loan', function($q) use ($concernId) {
            $q->where('lender_concern_id', $concernId)->where('status', 2);
        })->where('status', 1)->sum('total_amount');

        // Outstanding Payable (Loans Taken where status is Approved)
        $outstandingPayable = CompanyLoanInstallment::whereHas('loan', function($q) use ($concernId) {
            $q->where('borrower_concern_id', $concernId)->where('status', 2);
        })->where('status', 1)->sum('total_amount');

        return view('company_loan.dashboard', compact(
            'loansGiven',
            'loansTaken',
            'pendingRequests',
            'outstandingReceivable',
            'outstandingPayable'
        ));
    }
}
