<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiFdr\FdrAccount;
use App\Models\ShomriddhiFdr\FdrScheme;
use App\Models\ShomriddhiFdr\FdrTransaction;

class FdrDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_schemes' => FdrScheme::count(),
            'active_accounts' => FdrAccount::where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('status', 'Active')->count(),
            'pending_accounts' => FdrAccount::where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('status', 'Pending')->count(),
            'total_principal' => FdrAccount::where('origin_sister_concern_id', auth()->user()->activeConcernId())->whereIn('status', ['Active', 'Matured'])->sum('principal_amount'),
            'total_transactions' => FdrTransaction::whereHas('account', fn ($q) => $q->where('origin_sister_concern_id', auth()->user()->activeConcernId()))->count(),
            'recent_transactions' => FdrTransaction::with('account.customer')->whereHas('account', fn ($q) => $q->where('origin_sister_concern_id', auth()->user()->activeConcernId()))->whereDate('date', auth()->user()->business_date)->orderBy('date', 'desc')->take(5)->get(),
        ];

        return view('shomriddhi_fdr.dashboard', compact('data'));
    }
}
