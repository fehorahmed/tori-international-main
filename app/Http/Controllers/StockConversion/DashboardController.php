<?php

namespace App\Http\Controllers\StockConversion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockConversion\StockConversion;

class DashboardController extends Controller
{
    public function index()
    {
        $totalConversions = StockConversion::where('sister_concern_id', auth()->user()->activeConcernId())->count();
        $pendingConversions = StockConversion::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->count(); // Assuming 1 is pending
        $completedConversions = StockConversion::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 2)->count(); // Assuming 2 is completed
        
        $recentConversions = StockConversion::with('warehouse')->where('sister_concern_id', auth()->user()->activeConcernId())
            ->whereDate('date', auth()->user()->business_date)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('stock_conversion.dashboard', compact(
            'totalConversions', 
            'pendingConversions', 
            'completedConversions',
            'recentConversions'
        ));
    }
}
