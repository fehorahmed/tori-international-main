<?php

namespace App\Http\Controllers\SrManagement;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmClient;

use App\Models\Crm\CustomerType;
use App\Models\Crm\Lead;
use App\Models\Division;
use App\Models\Hrm\Employee;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Crm\Market;
use App\Models\SrManagement\SrVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Cache;
use App\Traits\UploadTrait;

class SrLeadCollectionController extends Controller
{
    use UploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('sister_concern_id',auth()->user()->activeConcernId())->orderBy('name')->get();
        
        return view('sr_management.sr_lead_collection.index',compact('users'));
    }

    public function dataTable(Request $request)
    {
        $query = Lead::where('sister_concern_id',auth()->user()->activeConcernId())
            ->whereNotNull('sr_id')
            ->with('user:id,name','crmClient.division', 'crmClient.district', 'crmClient.upazila', 'crmClient.union');
            
        if (auth()->user()->role == RoleType::$SR) {
            $query->where('sr_id', auth()->user()->employee_id);
        }

        if ($request->start_date != '' && $request->end_date != '') {
            $query->whereBetween('date', [Carbon::parse($request->start_date)->format('Y-m-d'), Carbon::parse($request->end_date)->format('Y-m-d')]);
        }



        if ($request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('image', function (Lead $lead) {
                return '<img height="50px" src="' . asset($lead->crmClient->image ?? 'img/no-image.webp') . '">';
            })
            ->addColumn('action', function(Lead $lead) {
                $btn = '';
                // if (auth()->user()->hasPermissionTo('sr_lead_collection_edit') && $lead->crm_status_id != 3) {
                //     $btn .= ' <a href="' . route('sr_lead_collection.edit', ['lead' => $lead->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                // }
                if (auth()->user()->hasPermissionTo('sr_lead_collection_delete')) {
                    $btn .= ' <a role="button" data-id="' . $lead->id . '" class="btn btn-danger bg-gradient-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                // Feedback button disabled as per request
                // if (auth()->user()->hasPermissionTo('lead_entry_feedback_update') && $lead->crm_status_id != 3) {
                //     $btn .= ' <a role="button" data-id="' . $lead->id . '" class="btn btn-success bg-gradient-success btn-add-feedback btn-sm"><i class="fa fa-feed"></i> Feedback</a>';
                // }
                return dropdownMenuContainer($btn);
            })

            ->addColumn('crm_client_name', function(Lead $lead) {
                return $lead->crmClient->name ?? '';
            })
            ->addColumn('crm_client_contact_no', function(Lead $lead) {
                return $lead->crmClient->contact_no ?? '';
            })
            ->addColumn('address', function(Lead $lead) {
                $client = $lead->crmClient;
                if (!$client) return '<span class="badge badge-secondary">N/A</span>';
                
                $addr = [];
                if ($client->market) $addr[] = $client->market->name;
                if ($client->village) $addr[] = $client->village->name;
                if ($client->union) $addr[] = $client->union->name;
                if ($client->pourashava) $addr[] = $client->pourashava->name;
                if ($client->upazila) $addr[] = $client->upazila->name;
                if ($client->district) $addr[] = $client->district->name;
                if ($client->division) $addr[] = $client->division->name;
                
                $fullAddress = !empty($addr) ? htmlspecialchars(implode(', ', array_filter($addr))) : 'N/A';

                // We combine Address + Live Location into a single button in the "Location Info" column
                return $fullAddress;
            })
            ->addColumn('location', function (Lead $lead) {
                $client = $lead->crmClient;
                if (!$client) {
                    return '<span class="badge badge-secondary">N/A</span>';
                }

                $addr = [];
                if ($client->market) $addr[] = $client->market->name;
                if ($client->village) $addr[] = $client->village->name;
                if ($client->union) $addr[] = $client->union->name;
                if ($client->pourashava) $addr[] = $client->pourashava->name;
                if ($client->upazila) $addr[] = $client->upazila->name;
                if ($client->district) $addr[] = $client->district->name;
                if ($client->division) $addr[] = $client->division->name;
                
                $fullAddress = !empty($addr) ? htmlspecialchars(implode(', ', array_filter($addr))) : 'N/A';
                
                $locName = $client->location ? htmlspecialchars($client->location) : 'Coordinates Only';
                $lat = $client->lat ?? '';
                $long = $client->long ?? '';

                return '<button class="btn btn-xs btn-outline-info btn-view-location" 
                            data-address="' . $fullAddress . '"
                            data-loc="' . $locName . '" 
                            data-lat="' . $lat . '" 
                            data-long="' . $long . '">
                            <i class="fas fa-map-marker-alt"></i> View Info
                        </button>';
            })
            ->addColumn('created_by', function (Lead $lead) {
                return $lead->user->name ?? 'N/A';
            })
            ->rawColumns(['action', 'image', 'address', 'location'])
            ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('lead_entry')) {
            abort(403, 'Unauthorized');
        }

        $activeMarkets = collect();
        if (auth()->user()->role == RoleType::$SR) {
            $today = date('l');
            $activePlans = RoutePlan::with('market')
                ->where('sr_id', auth()->user()->employee_id)
                ->where('day_of_week', $today)
                ->get();
            
            if ($activePlans->count() > 0) {
                $activeMarkets = $activePlans->pluck('market')->filter();
            }
        }

        $divisions = Division::orderBy('name')->get();

        
        $srs = Employee::srs()
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 1)
                ->orderBy('name')
                ->get();
        $customerTypes = CustomerType::where('status', 1)->get();
                
        return view('sr_management.sr_lead_collection.create',compact('divisions', 'activeMarkets', 'srs', 'customerTypes'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('lead_entry')) {
            abort(403, 'Unauthorized');
        }
        $request->validate([
            'client_name' => 'required|max:255',
            'client_contact_no' => 'required|max:255',
            'client_email' => 'nullable|email|max:255',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'market_id' => 'required',
            'village_id' => 'nullable|integer',
            'customer_type_id' => 'nullable|integer',

            'date' => 'required|date',
            'notes' => 'nullable|string',
            'image_base64' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $client = new CrmClient();
                $client->sister_concern_id = auth()->user()->activeConcernId();
                $client->name = $request->client_name;
                $client->contact_no = $request->client_contact_no;
                $client->email = $request->client_email;
                $client->division_id = $request->division;
                $client->district_id = $request->district;
                $client->upazila_id = $request->upazila;

                if ($request->union) {
                    if (str_contains($request->union, 'pourashava_')) {
                        $client->pourashava_id = str_replace('pourashava_', '', $request->union);
                        $client->union_id = null;
                    } elseif (str_contains($request->union, 'union_')) {
                        $client->union_id = str_replace('union_', '', $request->union);
                        $client->pourashava_id = null;
                    } else {
                        $client->union_id = $request->union; // For backwards compatibility
                        $client->pourashava_id = null;
                    }
                } else {
                    $client->union_id = null;
                    $client->pourashava_id = null;
                }

                $client->market_id = $request->market_id;
                $client->village_id = $request->village_id;
                $client->customer_type_id = $request->customer_type_id;
                $client->source = 1; // 1 = CRM Lead
                $client->lat = $request->lat;
                $client->long = $request->long;
                // Get human readable location if lat/long are present
                if ($request->lat && $request->long) {
                    $client->location = $this->getLocationName($request->lat, $request->long);
                } else {
                    $client->location = null;
                }

                if ($request->image_base64) {
                    $client->image = $this->uploadBase64Image($request->image_base64, 'crm/client', 800);
                }

                $client->save();
                $clientId = $client->id;

                $lead = new Lead();
                $lead->sister_concern_id = auth()->user()->activeConcernId();
                $lead->order_no = generateOrder(1);
                $lead->crm_client_id = $clientId;

                $lead->date = Carbon::parse($request->date)->format('Y-m-d');
                $lead->notes = $request->notes;
                $lead->assigned_to = auth()->id();
                $lead->user_id = auth()->id();
                $lead->user_id = auth()->id();
                
                if (auth()->user()->role == RoleType::$SR) {
                    $lead->sr_id = auth()->user()->employee_id ?? null;
                } else {
                    $lead->sr_id = $request->sr_id;
                }
                
                $lead->save();
            });
            return redirect()->route('sr_lead_collection.index')->with('message','Lead Collection created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error',$e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {

        if (!auth()->user()->hasPermissionTo('sr_lead_collection_edit')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();

        
        $srs = Employee::srs()
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 1)
                ->orderBy('name')
                ->get();
        $customerTypes = \App\Models\Crm\CustomerType::where('status', 1)->get();

        return view('sr_management.sr_lead_collection.edit',compact('lead','divisions', 'srs', 'customerTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        if (!auth()->user()->hasPermissionTo('sr_lead_collection_edit')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'client_name' => 'required|max:255',
            'client_contact_no' => 'required|max:255',
            'client_email' => 'nullable|email|max:255',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'market_id' => 'required',
            'village_id' => 'nullable|integer',
            'customer_type_id' => 'nullable|integer',

            'date' => 'required|date',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request,$lead) {
                $client = CrmClient::where('id',$lead->crm_client_id)->first();
                $client->name = $request->client_name;
                $client->contact_no = $request->client_contact_no;
                $client->email = $request->client_email;
                $client->division_id = $request->division;
                $client->district_id = $request->district;
                $client->upazila_id = $request->upazila;
                
                if ($request->union) {
                    if (str_contains($request->union, 'pourashava_')) {
                        $client->pourashava_id = str_replace('pourashava_', '', $request->union);
                        $client->union_id = null;
                    } elseif (str_contains($request->union, 'union_')) {
                        $client->union_id = str_replace('union_', '', $request->union);
                        $client->pourashava_id = null;
                    } else {
                        $client->union_id = $request->union; // For backwards compatibility
                        $client->pourashava_id = null;
                    }
                } else {
                    $client->union_id = null;
                    $client->pourashava_id = null;
                }

                $client->market_id = $request->market_id;
                $client->village_id = $request->village_id;
                $client->customer_type_id = $request->customer_type_id;

                if ($request->hasFile('image')) {
                    if ($client->image && file_exists(public_path($client->image))) {
                        unlink(public_path($client->image));
                    }
                    $file = $request->file('image');
                    $filename = Uuid::uuid1()->toString() . '.' . $file->extension();
                    $destinationPath = 'uploads/crm/client';
                    $file->move(public_path($destinationPath), $filename);
                    $client->image = 'uploads/crm/client/' . $filename;
                }

                $client->save();


                $lead->date = Carbon::parse($request->date)->format('Y-m-d');
                $lead->notes = $request->notes;
                $lead->assigned_to = auth()->id();
                
                if (auth()->user()->role == RoleType::$SR) {
                    $lead->sr_id = auth()->user()->employee_id ?? null;
                } else {
                    $lead->sr_id = $request->sr_id;
                }
                
                $lead->save();
            });
            return redirect()->route('sr_lead_collection.index')->with('message','Lead Collection updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error',$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        try {
            if (!auth()->user()->hasPermissionTo('sr_lead_collection_delete')) {
                abort(403, 'Unauthorized');
            }

            $lead->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Lead deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_OK);

        }
    }
    public function feedback(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('sr_lead_collection_feedback_update')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([

            'feedback'=>'required|max:255',
        ]);


        try {
            DB::transaction(function () use ($request) {
                $lead = Lead::find($request->lead_id);

                $lead->notes = $request->feedback;
                $lead->save();
            });

            return response()->json(['status'=>true,'message' => 'Lead feedback updated successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['status'=>false,'message' =>$e->getMessage()], 500);
        }
    }

    public function getNearbyClients(Request $request)
    {
        $lat = $request->lat;
        $long = $request->long;
        
        if (!$lat || !$long) {
            return response()->json([]);
        }

        // Determine active market for the SR
        $activeMarketId = null;
        if (auth()->check() && auth()->user()->role == RoleType::$SR) {
            $today = date('l');
            $activePlan = RoutePlan::where('sr_id', auth()->user()->employee_id)
                ->where('day_of_week', $today)
                ->first();
            if ($activePlan) {
                $activeMarketId = $activePlan->market_id;
            }
        }

        // Distance in km using Haversine formula
        $query = CrmClient::select('id', 'name', 'contact_no', 'division_id', 'district_id', 'upazila_id', 'union_id', 'village_id')
            ->selectRaw('( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( `long` ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance', [$lat, $long, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('long')
            ->having('distance', '<', 0.1); // Within 100 meters
            
        // Filter by active market if applicable
        if ($activeMarketId) {
            $query->where('market_id', $activeMarketId);
        }

        $clients = $query->orderBy('distance')
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    public function getLocationName($latitude, $longitude)
    {
        $cacheKey = "location_{$latitude}_{$longitude}";
        return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
            try {
                $service = app(GeocodingService::class);
                return $service->getAddress($latitude, $longitude);
            } catch (\Exception $e) {
                \Log::error("Geocoding failed: " . $e->getMessage());
            }

            return null;
        });
    }
}
