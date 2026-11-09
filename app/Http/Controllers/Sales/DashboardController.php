<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SalePayment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        // Total Sales Count
        $totalSalesCount = SaleOrder::where('sister_concern_id', $sisterConcernId)->count();

        // Total Sales Amount
        $totalSalesAmount = SaleOrder::where('sister_concern_id', $sisterConcernId)->sum('total');

        // Total Paid Amount
        $totalPaidAmount = SaleOrder::where('sister_concern_id', $sisterConcernId)->sum('paid');

        // Total Due Amount
        $totalDueAmount = SaleOrder::where('sister_concern_id', $sisterConcernId)->sum('due');

        // Recent Sales
        $recentSales = SaleOrder::with('customer')
            ->where('sister_concern_id', $sisterConcernId)
            ->whereDate('date', auth()->user()->business_date)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('sales.dashboard', compact(
            'totalSalesCount',
            'totalSalesAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'recentSales'
        ));
    }
}
