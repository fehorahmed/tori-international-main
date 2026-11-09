<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;
use App\Models\TraineeManagement\TraineeVisit;
use App\Models\TraineeManagement\TraineeVisitType;
use App\Models\TraineeManagement\TraineeVisitClient;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Crm\CrmClient;
use App\Enumeration\RoleType;
use App\Models\Hrm\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\GeocodingService;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class TraineeVisitController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits'), 403, 'Unauthorized');
        $traineesQuery = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->trainees();
        
        if (auth()->user()->role == 'Trainee' || auth()->user()->employee_id) {
            $traineesQuery->where('id', auth()->user()->employee_id);
        }
        $trainees = $traineesQuery->get();

        $visitTypes = TraineeVisitType::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->get();

        return view('trainee_management.visits.index', compact('trainees', 'visitTypes'));
    }

    public function dataTable(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits'), 403, 'Unauthorized');
        $query = TraineeVisit::with([
            'trainee', 'visitType',
            'customer.division', 'customer.district', 'customer.upazila', 'customer.union',
            'visitClient.division', 'visitClient.district', 'visitClient.upazila', 'visitClient.union', 'visitClient.village', 'visitClient.pourashava'
        ])
        ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (auth()->user()->employee_id) {
            $query->where('trainee_id', auth()->user()->employee_id);
        } elseif ($request->trainee_id) {
            $query->where('trainee_id', $request->trainee_id);
        }

        if ($request->trainee_visit_type_id) {
            $query->where('trainee_visit_type_id', $request->trainee_visit_type_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('trainee_name', function (TraineeVisit $visit) {
                return $visit->trainee->name ?? 'N/A';
            })
            ->addColumn('visit_type', function (TraineeVisit $visit) {
                return $visit->visitType->name ?? 'N/A';
            })
            ->addColumn('customer_name', function (TraineeVisit $visit) {
                $client = $visit->visitClient;
                if ($client) {
                    $type = $visit->customer_id ? '<span class="badge badge-success">Existing</span>' : '<span class="badge badge-info">New Prospect</span>';
                    return $type . '<br><b>' . $client->name . '</b><br>' . $client->mobile_no;
                }
                return 'N/A';
            })
            ->addColumn('date', function (TraineeVisit $visit) {
                return Carbon::parse($visit->date)->format('d-m-Y');
            })
            ->addColumn('address', function (TraineeVisit $visit) {
                $addr = [];
                $obj = $visit->visitClient;
                if ($obj) {
                    if ($obj->village) $addr[] = $obj->village->name ?? '';
                    if ($obj->union) $addr[] = $obj->union->name ?? '';
                    if ($obj->pourashava) $addr[] = $obj->pourashava->name ?? '';
                    if ($obj->upazila) $addr[] = $obj->upazila->name ?? '';
                    if ($obj->district) $addr[] = $obj->district->name ?? '';
                    if ($obj->division) $addr[] = $obj->division->name ?? '';
                }
                return !empty($addr) ? implode(', ', array_filter($addr)) : 'N/A';
            })
            ->addColumn('location', function (TraineeVisit $visit) {
                return $visit->location ?? 'N/A';
            })
            ->addColumn('image', function (TraineeVisit $visit) {
                if ($visit->image) {
                    $url = asset($visit->image);
                    return '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="60" class="img-thumbnail"></a>';
                }
                return 'No Image';
            })
            ->addColumn('gps', function (TraineeVisit $visit) {
                if ($visit->lat && $visit->long) {
                    return '<a href="https://www.google.com/maps?q=' . $visit->lat . ',' . $visit->long . '" target="_blank" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-map-marker-alt"></i> Map
                            </a>';
                }
                return 'N/A';
            })
            ->addColumn('action', function (TraineeVisit $visit) {
                return '<button type="button" class="btn btn-primary btn-xs btn-view" data-id="' . $visit->id . '">
                            <i class="fas fa-eye"></i> View
                        </button>';
            })
            ->rawColumns(['customer_name', 'image', 'gps', 'action'])
            ->make(true);
    }

    public function show(TraineeVisit $visit)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits'), 403, 'Unauthorized');
        $visit->load(['trainee', 'visitType', 'visitClient.division', 'visitClient.district', 'visitClient.upazila', 'visitClient.union', 'visitClient.pourashava']);
        return view('trainee_management.visits.show', compact('visit'));
    }

    public function create()
    {
        if (auth()->user()->role != 'Trainee') {
            abort(403, 'Unauthorized. Only Trainees can log visits.');
        }

        $user = auth()->user();
        $customers = CrmClient::where('sister_concern_id', $user->activeConcernId())->get();
        $divisions = Division::all();
        $visitTypes = TraineeVisitType::where('sister_concern_id', $user->activeConcernId())
            ->where('status', 1)
            ->orderBy('name')
            ->get();
        
        return view('trainee_management.visits.create', compact('customers', 'divisions', 'visitTypes'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role != 'Trainee') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only Trainees can log visits.'], 403);
        }

        $request->validate([
            'visit_type' => 'required|in:existing,new',
            'customer_id' => 'required_if:visit_type,existing',
            'name' => 'required_if:visit_type,new',
            'mobile_no' => 'required_if:visit_type,new',
            'division_id' => 'required_if:visit_type,new',
            'district_id' => 'required_if:visit_type,new',
            'image' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            if (!$user->employee_id) {
                return response()->json(['status' => 'error', 'message' => 'Your account is not linked to a Trainee record.'], 403);
            }
            
            $date = date('Y-m-d');
            $visitClientId = null;

            // Automation: Find task assignment for current month
            $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
            $assignment = TaskAssignment::where('employee_id', $user->employee_id)
                ->where('date', $currentMonthStart)
                ->first();

            $taskItemId = null;
            if ($assignment) {
                $visitTask = TaskItem::where('task_assignment_id', $assignment->id)
                    ->whereHas('type', function($q) {
                        $q->where('slug', 'visit_count');
                    })->first();

                if ($visitTask) {
                    $visitTask->increment('actual_value');
                    $taskItemId = $visitTask->id;
                }
            }

            // Create Visit Client record
            if ($request->visit_type == 'existing') {
                $customer = CrmClient::find($request->customer_id);
                if ($customer) {
                    $visitClient = TraineeVisitClient::create([
                        'sister_concern_id' => $user->activeConcernId(),
                        'trainee_id' => $user->employee_id,
                        'name' => $customer->name,
                        'mobile_no' => $customer->mobile_no,
                        'email' => $customer->email,
                        'division_id' => $customer->division_id,
                        'district_id' => $customer->district_id,
                        'upazila_id' => $customer->upazila_id,
                        'union_id' => $customer->union_id,
                        'village_id' => $customer->village_id,
                    ]);
                    $visitClientId = $visitClient->id;
                }
            } else {
                $clientData = [
                    'sister_concern_id' => $user->activeConcernId(),
                    'trainee_id' => $user->employee_id,
                    'name' => $request->name,
                    'mobile_no' => $request->mobile_no,
                    'email' => $request->email,
                    'division_id' => $request->division_id,
                    'district_id' => $request->district_id,
                    'upazila_id' => $request->upazila_id,
                    'village_id' => $request->village_id,
                ];

                if ($request->union_id) {
                    if (strpos($request->union_id, 'union_') !== false) {
                        $clientData['union_id'] = str_replace('union_', '', $request->union_id);
                    } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                        $clientData['pourashava_id'] = str_replace('pourashava_', '', $request->union_id);
                    } else {
                        $clientData['union_id'] = $request->union_id;
                    }
                }

                $visitClient = TraineeVisitClient::create($clientData);
                $visitClientId = $visitClient->id;
            }

            // Image handling
            $image_name = null;
            if ($request->image) {
                $data = $request->image;
                if (strpos($data, ';') !== false) {
                    list($type, $data) = explode(';', $data);
                    list(, $data)      = explode(',', $data);
                }
                $imgeData = base64_decode($data);
                $image_name = "/uploads/trainee_visits/" . Uuid::uuid1()->toString() . '.png';
                $path = public_path() . $image_name;
                
                if (!file_exists(public_path('/uploads/trainee_visits'))) {
                    mkdir(public_path('/uploads/trainee_visits'), 0777, true);
                }
                file_put_contents($path, $imgeData);
            }

            TraineeVisit::create([
                'sister_concern_id' => $user->activeConcernId(),
                'trainee_id' => $user->employee_id,
                'task_id' => $assignment ? $assignment->id : null,
                'task_item_id' => $taskItemId,
                'trainee_visit_type_id' => $request->trainee_visit_type_id,
                'customer_id' => $request->customer_id,
                'trainee_visit_client_id' => $visitClientId,
                'date' => $date,
                'image' => $image_name,
                'notes' => $request->notes,
                'lat' => $request->lat,
                'long' => $request->long,
                'location' => $request->location ?: $this->getLocationName($request->lat, $request->long),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Visit recorded and performance updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getLocationName($latitude, $longitude)
    {
        if (!$latitude || !$longitude) return null;
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
