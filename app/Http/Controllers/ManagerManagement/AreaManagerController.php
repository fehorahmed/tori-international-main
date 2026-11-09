<?php

namespace App\Http\Controllers\ManagerManagement;
use App\Http\Controllers\Controller;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\RoleType;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\UserManagement\Permission;
use App\Models\BranchManagement\SisterConcern;
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

class AreaManagerController extends Controller
{
    use UploadTrait;

    public function index()
    {
        return view('manager_management.area_manager.index');
    }

    public function dataTable()
    {
        $query = Employee::areaManagers()
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->with('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $areaManager) {
                $btn = '<a href="' . route('area-manager.show', ['area_manager' => $areaManager->id]) . '" class="btn btn-primary btn-sm mb-1" title="View"><i class="fa fa-eye"></i></a> ';
                $btn .= '<a href="' . route('employees.edit', ['employee' => $areaManager->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                return dropdownMenuContainer($btn);
            })
            ->addColumn('image', function (Employee $areaManager) {
                $url = asset('img/avatar.png');
                if ($areaManager->photo) {
                    $url = asset($areaManager->photo);
                }
                return '<img src="' . $url . '" width="50px" height="50px" class="img-circle" style="border: 1px solid #ddd;">';
            })
            ->addColumn('address', function (Employee $areaManager) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $areaManager->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['image', 'action', 'status', 'address'])
            ->toJson();
    }

    public function show(Employee $area_manager)
    {
        if (!$area_manager->isAreaManager()) {
            abort(403, 'Invalid Area Manager');
        }
        $area_manager->load('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava', 'currentVillage',
            'permanentDivision', 'permanentDistrict', 'permanentUpazila', 'permanentUnion', 'permanentPourashava', 'permanentVillage',
            'department', 'designation', 'shift', 'employeeStatus');
            
        return view('hrm.employees.profile', ['employee' => $area_manager]);
    }
}
