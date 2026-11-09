<?php

namespace App\Http\Controllers\SrManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SrManagement\SrVisit;
use App\Models\Hrm\Employee;
use App\Enumeration\RoleType;
use App\Enumeration\SupplierType;
use App\Models\Supplier;
use App\Models\RoutePlan\RoutePlan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $sisterConcernId = $user->activeConcernId();

        $routeQuery = RoutePlan::with(['market' => function($q) {
        }])
        ->where('sister_concern_id', $sisterConcernId)
        ->where('day_of_week', today()->format('l'));

        if ($user->role == RoleType::$SR) {
            $routeQuery->where('sr_id', $user->employee_id);
        }

        $routePlans = $routeQuery->get();

        $customerTypes = \App\Models\RoutePlan\RoutePlanCustomerType::where('sister_concern_id', $sisterConcernId)
            ->where('day_of_week', today()->format('l'));
            
        if ($user->role == RoleType::$SR) {
            $customerTypes->where('sr_id', $user->employee_id);
        }
        $customerTypes = $customerTypes->get()->groupBy('sr_id');

        $markets = [];
        foreach ($routePlans as $plan) {
            if ($plan->market) {
                $shopQuery = Supplier::where('market_id', $plan->market->id)
                            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$DEALER])
                            ->select('id', 'name', 'mobile_no', 'lat', 'long', 'address', 'image', 'customer_type_id');
                            
                $typesForSr = isset($customerTypes[$plan->sr_id]) ? $customerTypes[$plan->sr_id]->pluck('customer_type_id')->toArray() : [];
                
                if (count($typesForSr) > 0) {
                    $shopQuery->whereIn('customer_type_id', $typesForSr);
                }
                
                $shops = $shopQuery->get();
                            
                foreach ($shops as $shop) {
                    $visitQuery = SrVisit::where('supplier_id', $shop->id)
                                                ->whereDate('created_at', today());
                    if ($user->role == RoleType::$SR) {
                        $visitQuery->where('sr_id', $user->employee_id);
                    }
                    $shop->is_visited_today = $visitQuery->exists();
                }

                $plan->market->shops = $shops;
                // Add sr name to distinguish for admin
                $plan->market->assigned_sr = $plan->sr->name ?? 'Unknown SR';
                $markets[] = $plan->market;
            }
        }

        $totalActiveMarkets = count($markets);
        $totalAssignedShops = collect($markets)->sum(function($m) { return count($m->shops); });
        
        $totalVisitedTodayQuery = SrVisit::whereDate('created_at', today());
        if ($user->role == RoleType::$SR) {
            $totalVisitedTodayQuery->where('sr_id', $user->employee_id);
        } else {
            $totalVisitedTodayQuery->whereHas('sr', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            });
        }
        $totalVisitedToday = $totalVisitedTodayQuery->count();

        // Basic metrics for Admin/Manager
        $totalSrs = Employee::where('sister_concern_id', $sisterConcernId)
            ->srs()
            ->count();

        $totalVisits = SrVisit::whereHas('sr', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            })->count();
            
        $todayVisits = SrVisit::whereHas('sr', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            })->whereDate('created_at', today())->count();

        // Performance / Target logic
        $dailyVisitTargetPerSr = 30; // Target is 30 per SR
        $totalTargetVisits = $totalSrs * $dailyVisitTargetPerSr;
        $targetAchievementPercentage = $totalTargetVisits > 0 ? round(($todayVisits / $totalTargetVisits) * 100, 2) : 0;
        
        // Active Routes Today
        $todayName = today()->format('l'); // e.g. 'Monday'
        $activeRoutesCount = RoutePlan::where('sister_concern_id', $sisterConcernId)
            ->where('day_of_week', $todayName)
            ->count();

        // Get Top Active SRs Today
        $topSrsToday = SrVisit::with(['sr:id,name,employee_id'])
            ->whereHas('sr', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            })
            ->whereDate('created_at', today())
            ->select('sr_id', \DB::raw('count(*) as visit_count'))
            ->groupBy('sr_id')
            ->orderByDesc('visit_count')
            ->limit(5)
            ->get();

        return view('sr_management.dashboard', compact(
            'totalSrs', 
            'totalVisits', 
            'todayVisits', 
            'totalTargetVisits', 
            'targetAchievementPercentage', 
            'activeRoutesCount',
            'topSrsToday',
            'markets',
            'totalVisitedToday',
            'totalAssignedShops'
        ));
    }

    public function checkin(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'supplier_id' => 'required',
            'image' => 'required|image',
            'lat' => 'required',
            'long' => 'required'
        ]);

        try {
            \DB::beginTransaction();

            // Handle image upload
            $image_name = null;
            if ($request->hasFile('image')) {
                // Read the file and convert to base64
                $imagePath = $request->file('image')->getPathname();
                $base64Image = 'data:image/' . $request->file('image')->getClientOriginalExtension() . ';base64,' . base64_encode(file_get_contents($imagePath));
                
                // Using the trait's method (we need to inject UploadTrait or just write the file directly)
                $file = $request->file('image');
                $filename = time() . '_' . \Illuminate\Support\Str::random(5) . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/crm/client';
                if (!file_exists(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }
                $file->move(public_path($path), $filename);
                $image_name = $path . '/' . $filename;
            }

            // Get location name (simplified or just use coords)
            $location_address = "Lat: {$request->lat}, Lon: {$request->long}";

            \App\Models\SrManagement\SrVisit::create([
                'sister_concern_id' => $user->sister_concern_id,
                'sr_id' => $user->employee_id,
                'supplier_id' => $request->supplier_id,
                'date' => date('Y-m-d'),
                'image' => $image_name,
                'notes' => $request->notes,
                'lat' => $request->lat,
                'long' => $request->long,
                'location' => $location_address,
                'user_id' => $user->id,
            ]);

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Visit recorded successfully']);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
