<?php

namespace App\Http\Controllers\RoutePlan;

use App\Http\Controllers\Controller;
use App\Models\Crm\Market;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Crm\CrmClient;
use App\Models\SrManagement\SrVisit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        $totalMarkets = Market::where('status', 1)
            ->count();

        $totalRoutePlans = RoutePlan::where('sister_concern_id', $sisterConcernId)
            ->count();

        $totalShops = CrmClient::where('sister_concern_id', $sisterConcernId)
            ->whereNotNull('market_id')
            ->count();

        $todayVisits = SrVisit::where('sister_concern_id', $sisterConcernId)
            ->whereDate('date', today())
            ->count();

        return view('route_plan.dashboard', compact(
            'totalMarkets', 'totalRoutePlans', 'totalShops', 'todayVisits'
        ));
    }
}
