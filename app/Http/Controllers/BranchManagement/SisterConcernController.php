<?php

namespace App\Http\Controllers\BranchManagement;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchManagement\SisterConcernRequest;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use App\Models\Settings\Warehouse;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SisterConcernController extends Controller
{
    use UploadTrait;

    public function index(Request $request)
    {
        if (! auth()->user()->hasPermissionTo('branch_view')) {
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
            return redirect()->back()->withErrors(['error' => 'Something went wrong: '.$e->getMessage()]);
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
                    $btn .= '<a href="'.route('sister-concerns.show', ['sister_concern' => $sisterConcern->id]).'" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm btn-view" data-toggle="tooltip" title="View Details"><i class="fa fa-eye"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('branch_edit')) {
                    $btn .= '<a href="'.route('sister-concerns.edit', ['sister_concern' => $sisterConcern->id]).'" class="btn btn-info bg-gradient-info btn-sm btn-edit mt-1" data-toggle="tooltip" title="Edit"><i class="fa fa-edit"></i></a> ';
                }

                $btn .= '<a href="'.route('sister-concerns.certificate', ['sister_concern' => $sisterConcern->id]).'" target="_blank" class="btn btn-secondary bg-gradient-secondary btn-sm mt-1" data-toggle="tooltip" title="Certificate"><i class="fa fa-certificate"></i> </a>';

                return dropdownMenuContainer($btn);
            })

            ->addColumn('areaManager', function (SisterConcern $sisterConcern) {
                return $sisterConcern->areaManager->name ?? 'N/A';
            })
            ->addColumn('branchManager', function (SisterConcern $sisterConcern) {
                return $sisterConcern->branchManager->name ?? 'N/A';
            })
            ->addColumn('status', function (SisterConcern $sisterConcern) {
                if ($sisterConcern->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                } else {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->addColumn('logo', function (SisterConcern $sisterConcern) {
                if ($sisterConcern->logo == 1) {
                    return '<img src="'.asset($sisterConcern->logo).'" width="60px">';
                } else {
                    return '<img src="'.asset('img/placeholder_logo.png').'" width="60px">';
                }
            })

            ->rawColumns(['action', 'status', 'logo'])
            ->toJson();
    }

    public function create()
    {
        if (! auth()->user()->hasPermissionTo('branch_create')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch';
            $maxCode = SisterConcern::max('code');
            $nextCode = $maxCode >= 100 ? $maxCode + 1 : 100;

            return view('branch_management.sister_concern.create', compact('typeName', 'nextCode'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: '.$e->getMessage()]);
        }
    }

    public function store(SisterConcernRequest $request)
    {
        if (! auth()->user()->hasPermissionTo('branch_create')) {
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

            $sisterConcern = new SisterConcern;

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
            $sisterConcern->status = $request->status;
            $sisterConcern->save();

            // Create Default Warehouses (Main, Transit, Damage)
            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => 'Main Warehouse',
                'type' => 'main',
                'address' => $sisterConcern->address,
                'status' => 1,
            ]);

            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => 'Transit Warehouse',
                'type' => 'transit',
                'address' => $sisterConcern->address,
                'status' => 1,
            ]);

            Warehouse::create([
                'sister_concern_id' => $sisterConcern->id,
                'name' => 'Defect / Return Warehouse',
                'type' => 'damage',
                'address' => $sisterConcern->address,
                'status' => 1,
            ]);

            DB::commit();

            return redirect()->route('sister-concerns.index')->with('success', 'Sister Concern created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('sister-concerns.create')->with('error', 'An error occurred while creating the user : '.$e->getMessage())->withInput();
        }
    }

    public function show(SisterConcern $sisterConcern)
    {
        if (! auth()->user()->hasPermissionTo('branch_view')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch Details';

            return view('branch_management.sister_concern.show', compact('typeName', 'sisterConcern'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: '.$e->getMessage()]);
        }
    }

    public function edit(SisterConcern $sisterConcern)
    {
        if (! auth()->user()->hasPermissionTo('branch_edit')) {
            abort(403, 'Unauthorized');
        }

        try {
            $typeName = 'Branch';

            return view('branch_management.sister_concern.edit', compact('typeName', 'sisterConcern'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: '.$e->getMessage()]);
        }
    }

    public function update(SisterConcern $sisterConcern, SisterConcernRequest $request)
    {
        if (! auth()->user()->hasPermissionTo('branch_edit')) {
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
            $sisterConcern->status = $request->status;
            $sisterConcern->save();

            // Update or Create the 3 Warehouses (Main, Transit, Damage) dynamically by type
            // 1. Main Warehouse
            $mainWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'main')
                ->first();

            if ($mainWarehouse) {
                $mainWarehouse->update([
                    'name' => 'Main Warehouse',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => 'Main Warehouse',
                    'type' => 'main',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            }

            // 2. Transit Warehouse
            $transitWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'transit')
                ->first();

            if ($transitWarehouse) {
                $transitWarehouse->update([
                    'name' => 'Transit Warehouse',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => 'Transit Warehouse',
                    'type' => 'transit',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            }

            // 3. Damage Warehouse
            $damageWarehouse = Warehouse::where('sister_concern_id', $sisterConcern->id)
                ->where('type', 'damage')
                ->first();

            if ($damageWarehouse) {
                $damageWarehouse->update([
                    'name' => 'Defect / Return Warehouse',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            } else {
                Warehouse::create([
                    'sister_concern_id' => $sisterConcern->id,
                    'name' => 'Defect / Return Warehouse',
                    'type' => 'damage',
                    'address' => $request->address,
                    'status' => 1,
                ]);
            }

            DB::commit();

            return redirect()->route('sister-concerns.index')->with('success', 'Sister Concern created successfully');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('sister-concerns.create')->with('error', 'An error occurred while creating the user : '.$e->getMessage())->withInput();
        }
    }

    public function updateBusinessDate(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Manual business dates are disabled. The current Bangladesh date is used automatically.'], 410);
    }

    public function certificate(SisterConcern $sisterConcern)
    {
        $typeName = 'Branch Certificate';

        return view('branch_management.sister_concern.certificate', compact('sisterConcern', 'typeName'));
    }
}
