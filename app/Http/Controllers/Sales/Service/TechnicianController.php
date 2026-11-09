<?php

namespace App\Http\Controllers\Sales\Service;

use App\Enumeration\RoleType;
use App\Enumeration\ReservedAccountGroup;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\UserManagement\Permission;
use App\Models\User;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Shift;
use App\Models\Hrm\EmployeeStatus;
use App\Models\Hrm\Employee;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Ramsey\Uuid\Uuid;

class TechnicianController extends Controller
{
    use UploadTrait;

    public function index()
    {
        return view('sales.service.technician.index');
    }

    public function dataTable()
    {
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->technicians()
            ->with('currentDivision', 'currentDistrict', 'currentUpazila');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $technician) {
                $btn = '<a href="' . route('service.technician.show', ['technician' => $technician->id]) . '" class="btn btn-primary btn-sm mb-1" title="View"><i class="fa fa-eye"></i></a> ';
                if (auth()->user()->hasPermissionTo('technician_edit')) {
                    $btn .= '<a href="' . route('employees.edit', ['employee' => $technician->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('image', function (Employee $technician) {
                $url = asset('img/avatar.png');
                if ($technician->photo) {
                    $url = asset($technician->photo);
                }
                return '<img src="' . $url . '" width="50px" height="50px" class="img-circle" style="border: 1px solid #ddd;">';
            })
            ->addColumn('address', function (Employee $technician) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $technician->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['image', 'action', 'status', 'address'])
            ->toJson();
    }



    public function show(Employee $technician)
    {
        if (!$technician->isTechnician()) {
            abort(403, 'Invalid Technician');
        }
        abort_if($technician->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        $technician->load('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava');
        return view('sales.service.technician.show', compact('technician'));
    }


}
