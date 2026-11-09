<?php

namespace App\Http\Controllers\RoutePlan;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmClient;
use App\Models\Hrm\Employee;
use App\Models\Crm\Market;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Supplier;
use App\Models\SrManagement\SrVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Report 1: Route-wise Lead & Shop List
     */
    public function routeWiseLeads(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('report_route_leads')) {
            abort(403, 'Unauthorized');
        }

        $markets = Market::where('status', 1)
            ->orderBy('name')
            ->get();

        $clients = [];
        $selectedMarketId = $request->market_id;

        if ($selectedMarketId) {
            $clients = Supplier::whereIn('type', ['Customer', 'Corporate_Customer', 'Dealer']) // All 3 customer types
                ->where('market_id', $selectedMarketId)
                ->orderBy('name')
                ->get();
        }

        return view('route_plan.reports.route_leads', compact('markets', 'clients', 'selectedMarketId'));
    }

    /**
     * Report 2: SR Route Adherence & Coverage
     */
    public function routeCoverage(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('report_route_coverage')) {
            abort(403, 'Unauthorized');
        }

        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', RoleType::$SR)
            ->orderBy('name')
            ->get();

        $date = $request->date ?? date('d-m-Y');
        $parsedDate = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        $dayOfWeek = Carbon::createFromFormat('Y-m-d', $parsedDate)->format('l');
        $selectedSrId = $request->sr_id;

        $reportData = [];

        if ($request->has('date') || $request->has('sr_id')) {
            $query = RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('day_of_week', $dayOfWeek);

            if ($selectedSrId) {
                $query->where('sr_id', $selectedSrId);
            }

            $routePlans = $query->with(['sr', 'market'])->get();

            foreach ($routePlans as $plan) {
                // Total shops on this market
                $totalShops = Supplier::whereIn('type', ['Customer', 'Corporate_Customer', 'Dealer'])
                    ->where('market_id', $plan->market_id)
                    ->count();

                // Unique shop visits by this SR on this date, on this specific market
                $inRouteVisits = SrVisit::where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('sr_id', $plan->sr_id)
                    ->whereDate('date', $parsedDate)
                    ->whereHas('supplier', function($q) use ($plan) {
                        $q->where('market_id', $plan->market_id);
                    })
                    ->distinct('supplier_id')
                    ->count('supplier_id');

                // Visits by this SR on this date on OTHER markets (Out of Route)
                $outOfRouteVisits = SrVisit::where('sister_concern_id', auth()->user()->activeConcernId())
                    ->where('sr_id', $plan->sr_id)
                    ->whereDate('date', $parsedDate)
                    ->whereHas('supplier', function($q) use ($plan) {
                        $q->where('market_id', '!=', $plan->market_id)->orWhereNull('market_id');
                    })
                    ->distinct('supplier_id')
                    ->count('supplier_id');

                $coveragePercent = $totalShops > 0 ? round(($inRouteVisits / $totalShops) * 100, 2) : 0;

                $reportData[] = [
                    'sr_name' => $plan->sr->name ?? 'Unknown',
                    'market_name' => $plan->market->name ?? 'Unknown',
                    'total_shops' => $totalShops,
                    'in_route_visits' => $inRouteVisits,
                    'out_of_route_visits' => $outOfRouteVisits,
                    'coverage_percent' => $coveragePercent,
                ];
            }
        }

        return view('route_plan.reports.route_coverage', compact('srs', 'date', 'selectedSrId', 'dayOfWeek', 'reportData'));
    }

    /**
     * Report 3: Weekly Route Plans (PJP)
     */
    public function routePlans(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('report_weekly_route_plans')) {
            abort(403, 'Unauthorized');
        }

        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', RoleType::$SR)
            ->orderBy('name')
            ->get();

        $selectedSrId = $request->sr_id;

        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', RoleType::$SR)
            ->whereHas('routePlans')
            ->with(['routePlans.market.upazila'])
            ->orderBy('name');

        if ($selectedSrId) {
            $query->where('id', $selectedSrId);
        }

        $reportData = $query->get();

        return view('route_plan.reports.route_plans', compact('srs', 'selectedSrId', 'reportData'));
    }
}
