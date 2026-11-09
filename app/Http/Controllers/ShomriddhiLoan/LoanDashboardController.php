<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ShomriddhiLoan\LoanAccount;
use App\Models\ShomriddhiLoan\LoanTransaction;

class LoanDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_loan_amount' => LoanAccount::where('status', 'Approved')->sum('principal_amount'),
            'active_loans' => LoanAccount::where('status', 'Approved')->count(),
            'total_collection' => LoanTransaction::sum('amount'),
            'pending_loans' => LoanAccount::where('status', 'Pending')->count(),
            'recent_transactions' => LoanTransaction::with('loanAccount')->whereDate('collection_date', auth()->user()->business_date)->orderBy('collection_date', 'desc')->take(5)->get()
        ];
        return view('shomriddhi_loan.dashboard', compact('data'));
    }
}
