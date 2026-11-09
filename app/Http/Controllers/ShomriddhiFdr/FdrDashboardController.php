<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrTransaction;


class FdrDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_schemes' => FdrScheme::count(),
            'active_accounts' => FdrAccount::where('status', 'Active')->count(),
            'pending_accounts' => FdrAccount::where('status', 'Pending')->count(),
            'total_principal' => FdrAccount::whereIn('status', ['Active', 'Matured'])->sum('principal_amount'),
            'total_transactions' => FdrTransaction::count(),
            'recent_transactions' => FdrTransaction::with('account.customer')->whereDate('date', auth()->user()->business_date)->orderBy('date', 'desc')->take(5)->get()
        ];
        
        return view('shomriddhi_fdr.dashboard', compact('data'));
    }
}
