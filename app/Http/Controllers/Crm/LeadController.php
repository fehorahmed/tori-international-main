<?php

namespace App\Http\Controllers\Crm;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmClient;
use App\Models\Crm\CustomerType;
use App\Models\Crm\Lead;
use App\Models\Division;
use App\Models\RoutePlan\RoutePlan;
use App\Models\Crm\Market;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Cache;
use App\Traits\UploadTrait;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Supplier;
use App\Enumeration\ReservedAccountGroup;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    use UploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('sister_concern_id',auth()->user()->activeConcernId())->orderBy('name')->get();
        $customerTypes = CustomerType::where('status', 1)->get();
        $markets = Market::where('status', 1)->orderBy('name')->get();
        
        return view('crm.lead.index',compact('users', 'customerTypes', 'markets'));
    }

    public function dataTable(Request $request)
    {
        $query = Lead::where('sister_concern_id',auth()->user()->activeConcernId())
            ->with('user:id,name','crmClient.division', 'crmClient.district', 'crmClient.upazila', 'crmClient.union')
            ->orderBy('id','desc');

        if ($request->start_date != '' && $request->end_date != '') {
            $query->whereBetween('date', [Carbon::parse($request->start_date)->format('Y-m-d'), Carbon::parse($request->end_date)->format('Y-m-d')]);
        }
        if ($request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->customer_type_id != '' || $request->market_id != '') {
            $query->whereHas('crmClient', function ($q) use ($request) {
                if ($request->customer_type_id != '') {
                    $q->where('customer_type_id', $request->customer_type_id);
                }
                if ($request->market_id != '') {
                    $q->where('market_id', $request->market_id);
                }
            });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('image', function (Lead $lead) {
                return renderZoomGalleryImage($lead->crmClient->image ?? null, $lead->crmClient->name ?? '');
            })
            ->addColumn('action', function(Lead $lead) {
                $btn = '';
                // Edit button disabled for CRM Leads
                // if (auth()->user()->hasPermissionTo('lead_entry_edit') && $lead->crm_status_id != 3) {
                //     $btn .= ' <a href="' . route('leads.edit', ['lead' => $lead->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                // }
                if (auth()->user()->hasPermissionTo('lead_entry_delete')) {
                    $btn .= ' <a role="button" data-id="' . $lead->id . '" class="btn btn-danger bg-gradient-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                if ($lead->is_converted == 0) {
                    $btn .= ' <a role="button" data-id="' . $lead->id . '" class="btn btn-primary bg-gradient-primary btn-sm btn-convert"><i class="fa fa-exchange-alt"></i> Convert</a>';
                }
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

        $divisions = Division::orderBy('name')->get();
        $customerTypes = CustomerType::where('status', 1)->get();
        return view('crm.lead.create',compact('divisions', 'customerTypes'));
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
            'client_contact_no' => 'nullable|max:255|unique:crm_clients,contact_no',
            'client_email' => 'nullable|email|max:255',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'market_id' => 'required',
            'village_id' => 'nullable|integer',
            'customer_type_id' => 'required|integer',
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
                $lead->save();
            });
            return redirect()->route('leads.index')->with('message','Lead created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error',$e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead)
    {

        if (!auth()->user()->hasPermissionTo('lead_entry_edit')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();
        $customerTypes = CustomerType::where('status', 1)->get();
        return view('crm.lead.edit',compact('lead','divisions', 'customerTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        if (!auth()->user()->hasPermissionTo('lead_entry_edit')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'client_name' => 'required|max:255',
            'client_contact_no' => 'required|max:255|unique:crm_clients,contact_no,' . $lead->crm_client_id,
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
                $lead->save();
            });
            return redirect()->route('leads.index')->with('message','Lead updated successfully!');
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
            if (!auth()->user()->hasPermissionTo('lead_entry_delete')) {
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
        if (!auth()->user()->hasPermissionTo('lead_entry_feedback_update')) {
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
    public function getClientInfo(Lead $lead)
    {
        $client = $lead->crmClient;
        if (!$client) {
            return response()->json(['status' => false]);
        }
        $client->load('division', 'district', 'upazila', 'union', 'pourashava', 'village', 'market', 'customerType');
        return response()->json([
            'status' => true,
            'client' => $client
        ]);
    }

    public function convert(Request $request)
    {
        $request->validate([
            'lead_id' => 'required',
            'name' => 'required|max:255',
            'mobile_no' => [
                'required', 'max:255',
                Rule::unique('suppliers')
            ],
            'market_id' => 'required',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'customer_type_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $lead = Lead::findOrFail($request->lead_id);
            $client = $lead->crmClient;

            if (!$client) {
                return response()->json(['status' => false, 'message' => 'Lead does not have a client attached.']);
            }

            // If we are here, mobile_no is unique in suppliers table, safe to create.
                // Determine origin concern. If Lead doesn't have one, use current user's.
                $concernId = $lead->sister_concern_id ?? auth()->user()->activeConcernId();
                
                $supplier = new Supplier();
                $supplier->origin_sister_concern_id = $concernId;
                $supplier->type = RoleType::$CUSTOMER;
                $supplier->member_no = generatePartyMemberNo($concernId, $request->supplier_type);
                $supplier->name = $request->name;
                $supplier->mobile_no = $request->mobile_no;
                $supplier->email = $request->email;
                $supplier->market_id = $request->market_id;
                $supplier->division_id = $request->division;
                $supplier->district_id = $request->district;
                $supplier->upazila_id = $request->upazila;
                
                if ($request->union) {
                    if (strpos($request->union, 'union_') !== false) {
                        $supplier->union_id = str_replace('union_', '', $request->union);
                    } elseif (strpos($request->union, 'pourashava_') !== false) {
                        $supplier->pourashava_id = str_replace('pourashava_', '', $request->union);
                    }
                }
                
                $supplier->village_id = $request->village_id;
                $supplier->customer_type_id = $request->customer_type_id;
                $supplier->lat = $client->lat;
                $supplier->long = $client->long;
                $supplier->location = $client->location;
                $supplier->image = $client->image;
                $supplier->status = 1;
                $supplier->save();
                
                // Set Customer Account Head
                $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
                $accountHead->account_group_id = $receivablesGroup->id;
                $accountHead->supplier_id = $supplier->id;
                $accountHead->name = $supplier->name.'(Client)';
                $accountHead->nature = 'Debit';
                $accountHead->is_system = 1;
                $accountHead->is_control_account = 0;
                $accountHead->is_editable = 0;
                $accountHead->save();
                
                // Also update CRM client
                $client->name = $request->name;
                $client->contact_no = $request->mobile_no;
                $client->email = $request->email;
                $client->market_id = $request->market_id;
                $client->division_id = $request->division;
                $client->district_id = $request->district;
                $client->upazila_id = $request->upazila;
                $client->union_id = $supplier->union_id;
                $client->pourashava_id = $supplier->pourashava_id;
                $client->village_id = $request->village_id;
                $client->customer_type_id = $request->customer_type_id;
                $client->save();

            // Mark lead as converted
            $lead->is_converted = 1;
            $lead->save();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Successfully converted!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
