<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\Voucher;
use App\Models\Account\AccountHead;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('accounts'), 403, 'Unauthorized');

        $concernId = auth()->user()->activeConcernId();
        $today = Carbon::today()->format('Y-m-d');

        // Metrics for the dashboard
        $todayVouchersCount = Voucher::where('sister_concern_id', $concernId)
            ->where('date', $today)
            ->count();

        $totalActiveAccounts = AccountHead::where('status', 1)
            ->count();

        $recentVouchers = Voucher::where('sister_concern_id', $concernId)
            ->whereDate('date', auth()->user()->business_date)
            ->with(['paymentAccountHead', 'payeeDepositorAccountHead'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('accounts.dashboard', compact(
            'todayVouchersCount',
            'totalActiveAccounts',
            'recentVouchers'
        ));
    }
}
