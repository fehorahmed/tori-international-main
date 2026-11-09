<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Monitoring\Location;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('live_location') && !auth()->user()->hasPermissionTo('location_history'), 403, 'Unauthorized');
        $concernId = auth()->user()->activeConcernId();
        
        $totalTrackedUsers = User::where('sister_concern_id', $concernId)
            ->where('is_tracking_enabled', 1)
            ->count();
            
        $onlineUsers = User::where('sister_concern_id', $concernId)
            ->where('is_tracking_enabled', 1)
            ->where('is_online', 1)
            ->count();
            
        $todayLocations = Location::where('date', now()->format('Y-m-d'))
            ->whereHas('user', function($q) use ($concernId) {
                $q->where('sister_concern_id', $concernId);
            })
            ->count();

        return view('monitoring.dashboard', compact(
            'totalTrackedUsers',
            'onlineUsers',
            'todayLocations'
        ));
    }
}
