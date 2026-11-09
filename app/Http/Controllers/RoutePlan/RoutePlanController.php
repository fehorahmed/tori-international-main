<?php

namespace App\Http\Controllers\RoutePlan;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Crm\CustomerType;
use App\Models\Hrm\Employee;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Supplier;
use App\Models\Crm\Market;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoutePlanController extends Controller
{
    public function index()
    {
        return view('route_plan.route_plans.index');
    }

    public function dataTable()
    {
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->whereHas('routePlans')
            ->withCount('routePlans')
            ->orderBy('name');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('sr_name', function ($sr) {
                return $sr->name ?? '';
            })
            ->addColumn('total_routes', function ($sr) {
                return '<span class="badge badge-info" style="font-size:14px;">' . $sr->route_plans_count . ' Markets Assigned</span>';
            })
            ->addColumn('action', function ($sr) {
                $btn = '<button class="btn btn-info btn-sm btn-view-pjp" data-id="' . $sr->id . '"><i class="fa fa-eye"></i></button> ';
                if(auth()->user()->can('route_plan_edit')) {
                    $btn .= '<a href="'.route('route-plans.create', ['sr_id' => $sr->id]).'" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a> ';
                }
                if(auth()->user()->can('route_plan_delete')) {
                    $btn .= '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $sr->id . '"><i class="fa fa-trash"></i></button>';
                }
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['action', 'total_routes'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('route_plan_create')) {
            abort(403, 'Unauthorized');
        }

        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->orderBy('name')
            ->get();

        $markets = Market::where('status', 1)
            ->orderBy('name')
            ->get();

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        
        $customerTypes = CustomerType::where('status', 1)->get();

        $shopCounts = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereNotNull('customer_type_id')
            ->whereNotNull('market_id')
            ->selectRaw('market_id, customer_type_id, count(id) as count')
            ->groupBy('market_id', 'customer_type_id')
            ->get();

        $marketShopCounts = [];
        foreach ($shopCounts as $shopCount) {
            $marketShopCounts[$shopCount->market_id][$shopCount->customer_type_id] = $shopCount->count;
        }

        $selectedSrId = request('sr_id');

        return view('route_plan.route_plans.create', compact('srs', 'markets', 'days', 'selectedSrId', 'customerTypes', 'marketShopCounts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('route_plan_create')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'sr_id' => 'required',
            'plans' => 'nullable|array',
        ]);

        RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->delete();

        \App\Models\RoutePlan\RoutePlanCustomerType::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->delete();

        $assignedCount = 0;

        if ($request->has('plans') && is_array($request->plans)) {
            foreach ($request->plans as $dayOfWeek => $marketIds) {
                if (is_array($marketIds) && count($marketIds) > 0) {
                    foreach ($marketIds as $marketId) {
                        RoutePlan::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'sr_id' => $request->sr_id,
                            'market_id' => $marketId,
                            'day_of_week' => $dayOfWeek,
                        ]);
                        $assignedCount++;
                    }
                }
            }
        }

        if ($request->has('customer_types') && is_array($request->customer_types)) {
            foreach ($request->customer_types as $dayOfWeek => $typeIds) {
                if (is_array($typeIds) && count($typeIds) > 0) {
                    foreach ($typeIds as $typeId) {
                        \App\Models\RoutePlan\RoutePlanCustomerType::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'sr_id' => $request->sr_id,
                            'customer_type_id' => $typeId,
                            'day_of_week' => $dayOfWeek,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('route-plans.index')->with('message', "Successfully updated PJP. Total {$assignedCount} routes assigned for the week.");
    }

    public function getSrPlans(Request $request)
    {
        $plans = RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($group) {
                return $group->pluck('market_id')->toArray();
            });

        $customerTypes = \App\Models\RoutePlan\RoutePlanCustomerType::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($group) {
                return $group->pluck('customer_type_id')->toArray();
            });
            
        return response()->json([
            'markets' => $plans,
            'customer_types' => $customerTypes
        ]);
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

    public function edit(RoutePlan $route_plan)
    {
        if (!auth()->user()->hasPermissionTo('route_plan_edit')) {
            abort(403, 'Unauthorized');
        }

        if ($route_plan->sister_concern_id != auth()->user()->activeConcernId()) {
            abort(403);
        }

        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->orderBy('name')
            ->get();
            
        $markets = Market::where('status', 1)
            ->orderBy('name')
            ->get();

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        return view('route_plan.route_plans.edit', compact('route_plan', 'srs', 'markets', 'days'));
    }

    public function update(Request $request, RoutePlan $route_plan)
    {
        if (!auth()->user()->hasPermissionTo('route_plan_edit')) {
            abort(403, 'Unauthorized');
        }

        if ($route_plan->sister_concern_id != auth()->user()->activeConcernId()) {
            abort(403);
        }

        $request->validate([
            'sr_id' => 'required',
            'market_id' => 'required',
            'day_of_week' => 'required',
        ]);

        // Prevent duplicate plan for the same SR on the same day for the same market
        $exists = RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $request->sr_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('market_id', $request->market_id)
            ->where('id', '!=', $route_plan->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'This route plan already exists for the selected SR on ' . $request->day_of_week);
        }

        $route_plan->update([
            'sr_id' => $request->sr_id,
            'market_id' => $request->market_id,
            'day_of_week' => $request->day_of_week,
        ]);

        return redirect()->route('route-plans.index')->with('message', 'Route Plan updated successfully!');
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('route_plan_delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        RoutePlan::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('sr_id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'PJP completely deleted for this SR.'
        ]);
    }
}
