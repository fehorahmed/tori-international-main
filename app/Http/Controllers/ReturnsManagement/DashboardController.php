<?php

namespace App\Http\Controllers\ReturnsManagement;

use App\Http\Controllers\Controller;
use App\Models\ReturnsManagement\SalesReturn;
use App\Models\ReturnsManagement\PurchaseReturn;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->sister_concern_id;

        $salesReturnTotal = SalesReturn::where('status', 2)->where('sister_concern_id', $sisterConcernId)->sum('total_return_amount');
        $salesReturnPendingCount = SalesReturn::where('status', 1)->where('sister_concern_id', $sisterConcernId)->count();

        $purchaseReturnTotal = PurchaseReturn::where('status', 2)->where('sister_concern_id', $sisterConcernId)->sum('total_return_value');
        $purchaseReturnPendingCount = PurchaseReturn::where('status', 1)->where('sister_concern_id', $sisterConcernId)->count();

        $recentSalesReturns = SalesReturn::with('customer')
            ->where('sister_concern_id', $sisterConcernId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentPurchaseReturns = PurchaseReturn::with('supplier')
            ->where('sister_concern_id', $sisterConcernId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('returns_management.dashboard', compact(
            'salesReturnTotal', 
            'salesReturnPendingCount',
            'purchaseReturnTotal',
            'purchaseReturnPendingCount',
            'recentSalesReturns',
            'recentPurchaseReturns'
        ));
    }
}
