<?php

namespace App\Http\Controllers\SrManagement;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Crm\Market;
use App\Models\RoutePlan\RoutePlan;
use App\Models\RoutePlan\RoutePlanCustomerType;
use App\Models\SrManagement\SrVisit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SrRoutePlanController extends Controller
{
    public function index()
    {
        if (auth()->user()->role == RoleType::$SR) {
            $today = date('l'); // Current day of week, e.g., 'Saturday'
            
            $marketIds = RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('sr_id', auth()->user()->employee_id)
                ->where('day_of_week', $today)
                ->pluck('market_id')
                ->toArray();
                
            $customerTypes = RoutePlanCustomerType::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('sr_id', auth()->user()->employee_id)
                ->where('day_of_week', $today)
                ->pluck('customer_type_id')
                ->toArray();

            $markets = Market::with(['shops' => function($q) use ($customerTypes) {
                if (count($customerTypes) > 0) {
                    $q->whereIn('customer_type_id', $customerTypes);
                }
            }])->whereIn('id', $marketIds)->get();

            $visitedSupplierIds = SrVisit::where('sr_id', auth()->user()->employee_id)
                ->where('date', date('Y-m-d'))
                ->pluck('supplier_id')
                ->toArray();

            foreach ($markets as $market) {
                foreach ($market->shops as $shop) {
                    $shop->is_visited_today = in_array($shop->id, $visitedSupplierIds);
                }
            }
                
            return view('sr_management.sr_route_plans.sr_index', compact('markets', 'today'));
        }

        return view('sr_management.sr_route_plans.index');
    }

    public function mapView($marketId)
    {
        $market = \App\Models\Crm\Market::with('shops')->findOrFail($marketId);
        return view('sr_management.sr_route_plans.map', compact('market'));
    }

    public function dataTable()
    {
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->whereHas('routePlans')
            ->withCount('routePlans')
            ->orderBy('name');

        if (auth()->user()->role == RoleType::$SR) {
            $query->where('id', auth()->user()->employee_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('sr_name', function ($sr) {
                return $sr->name ?? '';
            })
            ->addColumn('total_routes', function ($sr) {
                return '<span class="badge badge-info" style="font-size:14px;">' . $sr->route_plans_count . ' Markets Assigned</span>';
            })
            ->addColumn('action', function ($sr) {
                return '<button class="btn btn-info btn-sm btn-view-pjp" data-id="' . $sr->id . '"><i class="fa fa-eye"></i> View PJP</button>';
            })
            ->rawColumns(['action', 'total_routes'])
            ->toJson();
    }

    public function viewSrPjp(Request $request)
    {
        $plans = RoutePlan::with('market.upazila')
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->get()
            ->groupBy('day_of_week');

        $customerTypes = \App\Models\RoutePlan\RoutePlanCustomerType::with('customerType')
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->get()
            ->groupBy('day_of_week');

        $sr = Employee::find($request->sr_id);
        
        $html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
        $html .= '<thead><tr><th style="width: 150px;">Day of Week</th><th>Assigned Markets & Upazilas</th><th>Target Outlets</th></tr></thead><tbody>';

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        
        foreach ($days as $day) {
            $html .= '<tr>';
            $html .= '<td><strong>' . $day . '</strong></td>';
            
            if ($plans->has($day) && $plans[$day]->count() > 0) {
                $html .= '<td>';
                $marketIds = [];
                foreach ($plans[$day] as $plan) {
                    $marketIds[] = $plan->market_id;
                    $marketName = $plan->market->name ?? 'Unknown';
                    $upazilaName = $plan->market->upazila->name ?? 'Unknown';
                    $html .= '<span class="badge badge-primary mr-1 mb-1" style="font-size: 13px;">' . $marketName . ' <small class="text-warning">[' . $upazilaName . ']</small></span>';
                }
                $html .= '</td>';

                // Target Outlets Logic
                $types = $customerTypes->has($day) ? $customerTypes[$day] : collect();
                $typeIds = $types->pluck('customer_type_id')->toArray();
                
                $query = \App\Models\Supplier::whereIn('market_id', $marketIds)
                    ->where('origin_sister_concern_id', $sr->sister_concern_id);
                    
                if (count($typeIds) > 0) {
                    $query->whereIn('customer_type_id', $typeIds);
                    $typesNames = $types->map(function($t) { return $t->customerType->name ?? ''; })->filter()->implode(', ');
                    $targetText = "<small class='d-block text-muted mb-1'>Types: {$typesNames}</small>";
                } else {
                    $targetText = "<small class='d-block text-muted mb-1'>Types: All</small>";
                }
                
                $shopCount = $query->count();
                $html .= '<td class="text-center">';
                $html .= $targetText;
                $html .= '<span class="badge badge-success" style="font-size: 14px;">' . $shopCount . ' Shops</span>';
                $html .= '</td>';
                
            } else {
                $html .= '<td colspan="2"><span class="text-muted"><i class="fa fa-ban"></i> No Route Assigned (Off-day)</span></td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></div>';

        return response()->json([
            'title' => 'Weekly PJP for ' . ($sr->name ?? ''),
            'html' => $html
        ]);
    }
}
