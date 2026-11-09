<?php

namespace App\Http\Controllers\Utilize;

use App\Http\Controllers\Controller;
use App\Models\Utilize\ProductUtilize;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('product_utilize'), 403, 'Unauthorized');
        
        $concernId = auth()->user()->activeConcernId();
        
        $totalUtilize = ProductUtilize::where('sister_concern_id', $concernId)->count();
        
        $todayUtilize = ProductUtilize::where('sister_concern_id', $concernId)
            ->whereDate('date', Carbon::today())
            ->count();
            
        $recentUtilizes = ProductUtilize::where('sister_concern_id', $concernId)
            ->whereDate('date', auth()->user()->business_date)
            ->with('user', 'warehouse')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('product_utilize.dashboard', compact(
            'totalUtilize',
            'todayUtilize',
            'recentUtilizes'
        ));
    }
}
