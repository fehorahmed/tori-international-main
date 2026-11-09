<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\SalaryProcess;
use App\Models\Payroll\SalaryProcessItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $concernId = auth()->user()->activeConcernId();
        
        $totalProcesses = SalaryProcess::where('sister_concern_id', $concernId)->count();
        $totalPaidItems = SalaryProcessItem::where('sister_concern_id', $concernId)->whereNotNull('voucher_id')->count();
        $totalUnpaidItems = SalaryProcessItem::where('sister_concern_id', $concernId)->whereNull('voucher_id')->count();
        $totalNetPay = SalaryProcessItem::where('sister_concern_id', $concernId)->sum('net_pay');
        
        $recentProcesses = SalaryProcess::where('sister_concern_id', $concernId)
                            ->whereDate('process_date', auth()->user()->business_date)
                            ->latest()
                            ->take(5)
                            ->get();

        return view('payroll.dashboard', compact(
            'totalProcesses', 
            'totalPaidItems', 
            'totalUnpaidItems', 
            'totalNetPay',
            'recentProcesses'
        ));
    }
}
