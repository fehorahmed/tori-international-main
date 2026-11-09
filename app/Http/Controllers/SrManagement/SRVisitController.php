<?php

namespace App\Http\Controllers\SrManagement;

use App\Http\Controllers\Controller;
use App\Models\RoutePlan\RoutePlan;
use App\Models\SrManagement\SrVisit;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Enumeration\RoleType;
use App\Models\Supplier;
use App\Enumeration\SupplierType;
use App\Models\Hrm\Employee;
use App\Models\User;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class SRVisitController extends Controller
{
    use UploadTrait;

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_visits'), 403, 'Unauthorized');
        $srsQuery = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs();
        
        if (auth()->user()->role == 'SR') {
            $srsQuery->where('id', auth()->user()->employee_id);
        }
        $srs = $srsQuery->get();

        return view('sr_management.sr.visits.index', compact('srs'));
    }

    public function dataTable(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_visits'), 403, 'Unauthorized');
        $query = SrVisit::with([
            'sr', 'user',
            'supplier'
        ])
        ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (auth()->user()->role == RoleType::$SR) {
            $query->where('sr_id', auth()->user()->employee_id);
        } elseif ($request->sr_id) {
            $query->where('sr_id', $request->sr_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (SrVisit $visit) {
                return '';
            })
            ->addColumn('sr_name', function (SrVisit $visit) {
                return $visit->sr->name ?? 'N/A';
            })
            ->addColumn('customer_name', function (SrVisit $visit) {
                $client = $visit->supplier;
                if ($client) {
                    $type = '<span class="badge badge-success">Customer</span>';
                    return $type . '<br><b>' . $client->name . '</b><br>' . $client->mobile_no;
                }
                return 'N/A';
            })
            ->addColumn('date', function (SrVisit $visit) {
                return Carbon::parse($visit->date)->format('d-m-Y');
            })
            ->addColumn('location', function (SrVisit $visit) {
                return $visit->location ?? 'N/A';
            })
            ->addColumn('address', function (SrVisit $visit) {
                return $visit->supplier->address ?? 'N/A';
            })
            ->addColumn('image', function (SrVisit $visit) {
                if ($visit->image) {
                    $url = asset($visit->image);
                    return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="60" class="img-thumbnail"></a>';
                }
                return 'No Image';
            })
            ->addColumn('created_by', function (SrVisit $visit) {
                return $visit->user->name ?? 'N/A';
            })
            ->addColumn('gps', function (SrVisit $visit) {
                if ($visit->lat && $visit->long) {
                    return '<a href="https://www.google.com/maps?q=' . $visit->lat . ',' . $visit->long . '" target="_blank" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-map-marker-alt"></i> Map
                            </a>';
                }
                return 'N/A';
            })
            ->rawColumns(['customer_name', 'image', 'gps', 'action'])
            ->make(true);
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_visits_create'), 403, 'Unauthorized');
        $user = auth()->user();

        $activeRoads = collect();
        $srs = collect();
        
        if ($user->role == RoleType::$SR) {
            $today = date('l');
            $activePlans = RoutePlan::with('market')
                ->where('sr_id', $user->employee_id)
                ->where('day_of_week', $today)
                ->get();
            
            if ($activePlans->count() > 0) {
                $activeRoads = $activePlans->pluck('market')->filter();
            }
        } else {
            $srs = Employee::where('sister_concern_id', $user->activeConcernId())
                ->srs()
                ->where('status', 1)
                ->get();
        }

        $customers = Supplier::whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$DEALER])
            ->get();
        $divisions = Division::all();
        
        return view('sr_management.sr.visits.create', compact('customers', 'divisions', 'activeRoads', 'srs'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_visits_create'), 403, 'Unauthorized');
        $user = auth()->user();
        
        $request->validate([
            'sr_id' => $user->role == RoleType::$SR ? 'nullable' : 'required',
            'supplier_id' => $user->role == RoleType::$SR ? 'nullable' : 'required',
            's' => $user->role == RoleType::$SR ? 'required' : 'nullable',
            'image' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $actualSrId = $user->role == RoleType::$SR ? $user->employee_id : $request->sr_id;
            
            if (!$actualSrId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please select an SR or ensure your account is linked to an Employee record.'
                ], 403);
            }

            $date = date('Y-m-d');
            
            // 1. PJP Validation (Only for SRs)
            $actualSupplierId = $request->supplier_id;

            if ($user->role == RoleType::$SR) {
                try {
                    $actualSupplierId = decrypt($request->s);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid Security Token!'
                    ], 403);
                }

                $today = date('l');
                $isAssigned = \App\Models\RoutePlan\RoutePlan::where('sr_id', $actualSrId)
                    ->where('day_of_week', $today)
                    ->whereHas('market.shops', function ($q) use ($actualSupplierId) {
                        $q->where('suppliers.id', $actualSupplierId);
                    })
                    ->exists();

                if (!$isAssigned) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Security Error: This shop is not in your assigned Route Plan (PJP) for today!'
                    ], 403);
                }
            }

            // 2. Geofence Validation
            $shop = \App\Models\Supplier::find($actualSupplierId);
            if ($shop && $shop->lat && $shop->long && $request->lat && $request->long) {
                $earthRadius = 6371000; // meters
                $latFrom = deg2rad($request->lat);
                $lonFrom = deg2rad($request->long);
                $latTo = deg2rad($shop->lat);
                $lonTo = deg2rad($shop->long);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                $distance = $angle * $earthRadius;

                if ($distance > 50) { // 50 meters max
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Geofence Error: You are ' . round($distance) . ' meters away from the shop. You must be within 50 meters to Check-in!'
                    ], 403);
                }
            }

            $image_name = null;
            if ($request->image) {
                $image_name = $this->uploadBase64Image($request->image, 'crm/client', 800);
            }

            // Get live location name automatically
            $location_address = $this->getLocationName($request->lat, $request->long);

            SrVisit::create([
                'sister_concern_id' => $user->sister_concern_id,
                'sr_id' => $actualSrId,
                'supplier_id' => $actualSupplierId,
                'date' => $date,
                'image' => $image_name,
                'notes' => $request->notes,
                'lat' => $request->lat,
                'long' => $request->long,
                'location' => $location_address,
                'user_id' => $user->id,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Visit recorded successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDistricts($divisionId)
    {
        return District::where('division_id', $divisionId)->get();
    }

    public function getUpazilas($districtId)
    {
        return Upazila::where('district_id', $districtId)->get();
    }

    public function getUnions($upazilaId)
    {
        return Union::where('upazila_id', $upazilaId)->get();
    }

    public function getActiveRoads($srId)
    {
        $today = date('l');
        $activePlans = RoutePlan::with('market.upazila')
            ->where('sr_id', $srId)
            ->where('day_of_week', $today)
            ->get();
        
        $roads = [];
        if ($activePlans->count() > 0) {
            foreach ($activePlans as $plan) {
                if ($plan->market) {
                    $roads[] = [
                        'id' => $plan->market->id,
                        'name' => $plan->market->name . ' (' . ($plan->market->upazila->name ?? '') . ')'
                    ];
                }
            }
        }
        return response()->json($roads);
    }

    public function getLocationName($latitude, $longitude)
    {
        $cacheKey = "location_{$latitude}_{$longitude}";
        return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
            try {
                $service = app(GeocodingService::class);
                $locationName = $service->getAddress($latitude, $longitude);
                if ($locationName) {
                    return $locationName;
                }
                return "Lat: {$latitude}, Lon: {$longitude}";
            } catch (\Exception $e) {
                return "Lat: {$latitude}, Lon: {$longitude}";
            }
        });
    }
}
