<?php

namespace App\Http\Controllers\BranchManagement;

use App\Http\Controllers\Controller;
use App\Enumeration\RoleType;
use App\Enumeration\SupplierType;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Settings\Warehouse;
use App\Traits\UploadTrait;
use App\Http\Requests\BranchManagement\SisterConcernRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class SisterConcernController extends Controller
{
    use UploadTrait;

    public function index(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_view')) {
            abort(403, 'Unauthorized');
        }
        if (auth()->user()->role == RoleType::$AREA_MANAGER) {
            $areaManagers = Employee::where('id', auth()->user()->employee_id)->areaManagers()->where('status', 1)->get();
        } else {
            $areaManagers = Employee::areaManagers()->where('status', 1)->get();
        }

        try {
            $typeName = 'Branch';

            return view('branch_management.sister_concern.index', compact('typeName', 'areaManagers'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function dataTable()
    {
        $query = SisterConcern::with(['areaManager', 'branchManager']);
        if (auth()->user()->role == RoleType::$AREA_MANAGER) {
            $query->where('area_manager_id', auth()->user()->employee_id);
        }
        if (request()->input('area_manager_id') != '') {
            $query->where('area_manager_id', request()->input('area_manager_id'));
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (SisterConcern $sisterConcern) {
                $btn = '';
                if (auth()->user()->hasPermissionTo('branch_view')) {
                    $btn .= '<a href="' . route('sister-concerns.show', ['sister_concern' => $sisterConcern->id]) . '" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm btn-view" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('branch_edit')) {
                    $btn .= '<a href="' . route('sister-concerns.edit', ['sister_concern' => $sisterConcern->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit mt-1" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('branch_business_date_change')) {
                    $btn .= '<button data-id="' . $sisterConcern->id . '" data-name="' . $sisterConcern->name . '" data-date="' . date('d-m-Y', strtotime($sisterConcern->business_date)) . '" class="btn btn-warning bg-gradient-warning btn-sm btn-change-date mt-1" data-toggle="tooltip" title="Change Business Date"><i class="fa fa-calendar"></i></button> ';
                }
                $btn .= '<a href="' . route('sister-concerns.certificate', ['sister_concern' => $sisterConcern->id]) . '" target="_blank" class="btn btn-secondary bg-gradient-secondary btn-sm mt-1" data-toggle="tooltip" title="Certificate"><i class="fa fa-certificate"></i> </a>';
                return dropdownMenuContainer($btn);
            })
            ->addColumn('business_date', function (SisterConcern $sisterConcern) {
                return date('d-m-Y', strtotime($sisterConcern->business_date));
            })
            ->addColumn('areaManager', function (SisterConcern $sisterConcern) {
                return $sisterConcern->areaManager->name ?? "N/A";
            })
            ->addColumn('branchManager', function (SisterConcern $sisterConcern) {
                return $sisterConcern->branchManager->name ?? "N/A";
            })
            ->addColumn('status', function (SisterConcern $sisterConcern) {
                if ($sisterConcern->status == 1)
                    return '<span class="badge badge-success">Active</span>';
                else
                    return '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('logo', function (SisterConcern $sisterConcern) {
                if ($sisterConcern->logo == 1)
                    return '<img src="' . asset($sisterConcern->logo) . '" width="60px">';
                else
                    return '<img src="' . asset('img/placeholder_logo.png') . '" width="60px">';
            })

            ->rawColumns(['action', 'status', 'logo'])
            ->toJson();
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('branch_create')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch';
            $maxCode = SisterConcern::max('code');
            $nextCode = $maxCode >= 100 ? $maxCode + 1 : 100;

            return view('branch_management.sister_concern.create', compact('typeName', 'nextCode'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function store(SisterConcernRequest $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_create')) {
            abort(403, 'Unauthorized');
        }
        // Start a database transaction
        DB::beginTransaction();

        try {
            $logoPath = null;
            if ($request->file('logo')) {
                $logoPath = $this->uploadImage($request->file('logo'), 'sister_concern/logo');
            }

            $authSignaturePath = null;
            if ($request->file('auth_signature')) {
                $authSignaturePath = $this->uploadImage($request->file('auth_signature'), 'sister_concern/signatures');
            }

            $officialSealPath = null;
            if ($request->file('official_seal')) {
                $officialSealPath = $this->uploadImage($request->file('official_seal'), 'sister_concern/seals');
            }

            $sisterConcern = new SisterConcern();

            $maxCode = SisterConcern::max('code');
            $sisterConcern->code = $maxCode >= 100 ? $maxCode + 1 : 100;

            $sisterConcern->name = $request->name;
            $sisterConcern->logo = $logoPath;
            $sisterConcern->auth_signature = $authSignaturePath;
            $sisterConcern->official_seal = $officialSealPath;
            $sisterConcern->trade_license_no = $request->trade_license_no;
            $sisterConcern->tin_no = $request->tin_no;
            $sisterConcern->bin_no = $request->bin_no;
            $sisterConcern->contact = $request->contact;
            $sisterConcern->email = $request->email;
            $sisterConcern->address = $request->address;
            $sisterConcern->district = $request->district;
            $sisterConcern->upazila = $request->upazila;
            $sisterConcern->post_code = $request->post_code;
            $sisterConcern->office_rent = $request->office_rent;
            $sisterConcern->electricity_bill = $request->electricity_bill;
            $sisterConcern->establishment_date = $request->establishment_date ? Carbon::parse($request->establishment_date)->format('Y-m-d') : null;
            $sisterConcern->certificate_expiry_date = $request->certificate_expiry_date ? Carbon::parse($request->certificate_expiry_date)->format('Y-m-d') : null;
            $sisterConcern->business_date = Carbon::parse($request->business_date)->format('Y-m-d');
            $sisterConcern->status = $request->status;
            $sisterConcern->save();

            // Create Default Warehouses (Main, Transit, Damage)
            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => "Main Warehouse",
                'type' => "main",
                'address' => $sisterConcern->address,
                'status' => 1
            ]);

            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => "Transit Warehouse",
                'type' => "transit",
                'address' => $sisterConcern->address,
                'status' => 1
            ]);

            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => "Defect / Return Warehouse",
                'type' => "damage",
                'address' => $sisterConcern->address,
                'status' => 1
            ]);

            DB::commit();

            return redirect()->route('sister-concerns.index')->with('success', 'Sister Concern created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('sister-concerns.create')->with('error', 'An error occurred while creating the user : ' . $e->getMessage())->withInput();
        }
    }

    public function show(SisterConcern $sisterConcern)
    {
        if (!auth()->user()->hasPermissionTo('branch_view')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch Details';
            return view('branch_management.sister_concern.show', compact('typeName', 'sisterConcern'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function edit(SisterConcern $sisterConcern)
    {
        if (!auth()->user()->hasPermissionTo('branch_edit')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch';

            return view('branch_management.sister_concern.edit', compact('typeName', 'sisterConcern'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function update(SisterConcern $sisterConcern, SisterConcernRequest $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_edit')) {
            abort(403, 'Unauthorized');
        }
        // Start a database transaction
        DB::beginTransaction();

        try {
            $logoPath = $sisterConcern->logo;
            if ($request->file('logo')) {
                // Upload Image
                $this->deleteFile($sisterConcern->logo);
                $logoPath = $this->uploadImage($request->file('logo'), 'sister_concern/logo');
            }

            $authSignaturePath = $sisterConcern->auth_signature;
            if ($request->file('auth_signature')) {
                // Upload Signature
                $this->deleteFile($sisterConcern->auth_signature);
                $authSignaturePath = $this->uploadImage($request->file('auth_signature'), 'sister_concern/signatures');
            }

            $officialSealPath = $sisterConcern->official_seal;
            if ($request->file('official_seal')) {
                // Upload Seal
                $this->deleteFile($sisterConcern->official_seal);
                $officialSealPath = $this->uploadImage($request->file('official_seal'), 'sister_concern/seals');
            }

            $sisterConcern->name = $request->name;
            $sisterConcern->code = $request->code;
            $sisterConcern->logo = $logoPath;
            $sisterConcern->auth_signature = $authSignaturePath;
            $sisterConcern->official_seal = $officialSealPath;
            $sisterConcern->trade_license_no = $request->trade_license_no;
            $sisterConcern->tin_no = $request->tin_no;
            $sisterConcern->bin_no = $request->bin_no;
            $sisterConcern->contact = $request->contact;
            $sisterConcern->email = $request->email;
            $sisterConcern->address = $request->address;
            $sisterConcern->district = $request->district;
            $sisterConcern->upazila = $request->upazila;
            $sisterConcern->post_code = $request->post_code;
            $sisterConcern->office_rent = $request->office_rent;
            $sisterConcern->electricity_bill = $request->electricity_bill;
            $sisterConcern->establishment_date = $request->establishment_date ? Carbon::parse($request->establishment_date)->format('Y-m-d') : null;
            $sisterConcern->certificate_expiry_date = $request->certificate_expiry_date ? Carbon::parse($request->certificate_expiry_date)->format('Y-m-d') : null;
            $sisterConcern->business_date = Carbon::parse($request->business_date)->format('Y-m-d');
            $sisterConcern->status = $request->status;
            $sisterConcern->save();

            // Update or Create the 3 Warehouses (Main, Transit, Damage) dynamically by type
            // 1. Main Warehouse
            $mainWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'main')
                ->first();

            if ($mainWarehouse) {
                $mainWarehouse->update([
                    'name' => "Main Warehouse",
                    'address' => $request->address,
                    'status' => 1
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => "Main Warehouse",
                    'type' => "main",
                    'address' => $request->address,
                    'status' => 1
                ]);
            }

            // 2. Transit Warehouse
            $transitWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'transit')
                ->first();

            if ($transitWarehouse) {
                $transitWarehouse->update([
                    'name' => "Transit Warehouse",
                    'address' => $request->address,
                    'status' => 1
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => "Transit Warehouse",
                    'type' => "transit",
                    'address' => $request->address,
                    'status' => 1
                ]);
            }

            // 3. Damage Warehouse
            $damageWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'damage')
                ->first();

            if ($damageWarehouse) {
                $damageWarehouse->update([
                    'name' => "Defect / Return Warehouse",
                    'address' => $request->address,
                    'status' => 1
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => "Defect / Return Warehouse",
                    'type' => "damage",
                    'address' => $request->address,
                    'status' => 1
                ]);
            }

            DB::commit();

            return redirect()->route('sister-concerns.index')->with('success', 'Sister Concern created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('sister-concerns.create')->with('error', 'An error occurred while creating the user : ' . $e->getMessage())->withInput();
        }
    }
    public function updateBusinessDate(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_business_date_change')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'id' => 'required|exists:sister_concerns,id',
            'business_date' => 'required|date',
        ]);

        try {
            $sisterConcern = SisterConcern::find($request->id);
            $sisterConcern->business_date = Carbon::parse($request->business_date)->format('Y-m-d');
            $sisterConcern->save();

            return response()->json(['success' => true, 'message' => 'Business date updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }



    public function certificate(SisterConcern $sisterConcern)
    {
        $typeName = 'Branch Certificate';
        return view('branch_management.sister_concern.certificate', compact('sisterConcern', 'typeName'));
    }
}
