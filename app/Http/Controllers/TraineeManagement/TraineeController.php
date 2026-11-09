<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;

use App\Enumeration\RoleType;
use App\Enumeration\ReservedAccountGroup;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\District;
use App\Models\Division;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeStatus;
use App\Models\Hrm\Shift;
use App\Models\UserManagement\Permission;
use App\Models\User;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class TraineeController extends Controller
{
    use UploadTrait;

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee'), 403, 'Unauthorized');
        return view('trainee_management.trainee.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee'), 403, 'Unauthorized');
        $query = Employee::with(['currentDivision', 'currentDistrict', 'currentUpazila'])
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->trainees();

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $trainee) {
                $btn = '<a href="' . route('trainee.show', ['trainee' => $trainee->id]) . '" class="btn btn-primary btn-sm mb-1" title="View"><i class="fa fa-eye"></i></a> ';
                if (auth()->user()->hasPermissionTo('trainee_edit')) {
                    $btn .= '<a href="' . route('employees.edit', ['employee' => $trainee->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('status', function (Employee $trainee) {
                return $trainee->status;
            })
            ->addColumn('address', function (Employee $trainee) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $trainee->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['action', 'address'])
            ->make(true);
    }



    public function show(Employee $trainee)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee'), 403, 'Unauthorized');
        if (!$trainee->isTrainee()) {
            abort(403, 'Invalid Trainee');
        }
        $trainee->load('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava');
        return view('trainee_management.trainee.show', compact('trainee'));
    }


}
