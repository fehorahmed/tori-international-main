<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;


class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('crm')) {
            abort(403, 'Unauthorized');
        }

        $activeConcernId = auth()->user()->activeConcernId();

        $totalLeads = Lead::where('sister_concern_id', $activeConcernId)->count();
        $todaysLeads = Lead::where('sister_concern_id', $activeConcernId)->whereDate('date', now()->toDateString())->count();
        
        $recentLeads = Lead::where('sister_concern_id', $activeConcernId)
            ->whereDate('date', auth()->user()->business_date)
            ->with(['crmClient'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('crm.dashboard', compact(
            'totalLeads', 
            'todaysLeads', 
            'recentLeads'
        ));
    }
}
