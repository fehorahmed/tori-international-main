<?php

namespace App\Http\Controllers\ShomriddhiDps;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiDps\DpsScheme;
use App\Models\ShomriddhiDps\DpsAccount;
use App\Models\ShomriddhiDps\DpsTransaction;

class DpsDashboardController extends Controller
{
    public function index()
    {
        $scId = auth()->user()->activeConcernId();

        $data = [
            'total_schemes'         => DpsScheme::where('status', 'Active')->count(),
            'active_accounts'       => DpsAccount::where('origin_sister_concern_id', $scId)->where('status', 'Active')->count(),
            'pending_accounts'      => DpsAccount::where('origin_sister_concern_id', $scId)->where('status', 'Pending')->count(),
            'matured_accounts'      => DpsAccount::where('origin_sister_concern_id', $scId)->where('status', 'Matured')->count(),
            'defaulted_accounts'    => DpsAccount::where('origin_sister_concern_id', $scId)->where('status', 'Defaulted')->count(),
            'total_deposited'       => DpsAccount::where('origin_sister_concern_id', $scId)->whereIn('status', ['Active', 'Matured'])->sum('total_deposited'),
            'total_payable_maturity'=> DpsAccount::where('origin_sister_concern_id', $scId)->where('status', 'Active')->sum('payable_at_maturity'),
            'recent_transactions'   => DpsTransaction::with('dpsAccount.customer')
                ->join('dps_accounts', 'dps_transactions.dps_account_id', '=', 'dps_accounts.id')
                ->where('dps_accounts.origin_sister_concern_id', $scId)
                ->select('dps_transactions.*')
                ->whereDate('dps_transactions.date', auth()->user()->business_date)
                ->orderBy('dps_transactions.date', 'desc')
                ->take(8)
                ->get(),
        ];

        return view('shomriddhi_dps.dashboard', compact('data'));
    }
}
