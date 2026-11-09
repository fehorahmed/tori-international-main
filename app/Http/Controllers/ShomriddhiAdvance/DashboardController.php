<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Account\AccountHead;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        // Total customers with savings account
        $totalMembers = ShomriddhiAccount::where('status',1)->count();

        // Total Pending Deposits
        $pendingDepositAmount = ShomriddhiTransaction::where('status', 'Pending')->where('type', 'deposit')->sum('amount');
        
        // Total Pending Withdrawals
        $pendingWithdrawAmount = ShomriddhiTransaction::where('status', 'Pending')->where('type', 'withdraw')->sum('amount');

        // Note: For total savings liability, we would sum the balances of all 'savings' account heads.
        // We'll calculate a basic aggregate.
        $totalLiability = 0; // This can be calculated properly via Account Transactions

        $recentTransactions = ShomriddhiTransaction::with(['customer', 'creator'])
            ->whereDate('date', auth()->user()->business_date)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('shomriddhi_advance.dashboard', compact('totalMembers', 'pendingDepositAmount', 'pendingWithdrawAmount', 'totalLiability', 'recentTransactions'));
    }
}
