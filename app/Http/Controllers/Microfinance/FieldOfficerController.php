<?php

namespace App\Http\Controllers\Microfinance;

use App\Enumeration\SupplierType;
use App\Enumeration\RoleType;
use App\Enumeration\ReservedAccountGroup;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EmployeeStatus;
use App\Models\Hrm\Shift;
use App\Models\Supplier;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\User;
use App\Models\UserManagement\Permission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\UploadTrait;
use Ramsey\Uuid\Uuid;
use App\Models\Sales\TaskAssignment;
use App\Models\Sales\TaskItem;
use App\Models\Sales\TaskType;
use App\Models\SrManagement\SrVisit;
use App\Models\Sales\SalePayment;

class FieldOfficerController extends Controller
{
    use UploadTrait;

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer'), 403, 'Unauthorized');
        return view('microfinance.field_officer.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer'), 403, 'Unauthorized');
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->fieldOfficers()
            ->with('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion');
        
        if (auth()->user()->role == RoleType::$FIELD_OFFICER) {
            $query->where('id', auth()->user()->employee_id);
        }    

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $fo) {
                $btn = '<a href="' . route('microfinance.field-officer.show', ['field_officer' => $fo->id]) . '" class="btn btn-primary btn-sm mb-1" title="View"><i class="fa fa-eye"></i></a> ';
                if (auth()->user()->hasPermissionTo('mf_field_officer_edit')) {
                    $btn .= '<a href="' . route('employees.edit', ['employee' => $fo->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('image', function (Employee $fo) {
                $url = asset('img/avatar.png');
                if ($fo->photo) {
                    $url = asset($fo->photo);
                }
                return '<img src="' . $url . '" width="50px" height="50px" class="img-circle" style="border: 1px solid #ddd;">';
            })
            ->addColumn('address', function (Employee $fo) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $fo->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['image', 'action', 'address'])
            ->toJson();
    }



    public function show(Employee $fieldOfficer)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_field_officer'), 403, 'Unauthorized');
        if (!$fieldOfficer->isFieldOfficer()) {
            abort(403, 'Invalid Field Officer');
        }
        abort_if($fieldOfficer->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        $fieldOfficer->load('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava', 'sisterConcern');
        
        $somitis = \App\Models\Microfinance\MfSomiti::where('field_officer_id', $fieldOfficer->id)
            ->withCount('members')
            ->get();

        return view('microfinance.field_officer.show', compact('fieldOfficer', 'somitis'));
    }


}
