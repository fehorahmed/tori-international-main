<?php

namespace App\Http\Controllers\SrManagement;

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

class SrController extends Controller
{
    use UploadTrait;
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('sr'), 403, 'Unauthorized');
        return view('sr_management.sr.index');
    }

    public function dataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('sr'), 403, 'Unauthorized');
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->with('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion');
        
        if(auth()->user()->role == RoleType::$SR){
            $query->where('id', auth()->user()->employee_id);
        }    

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Employee $sr) {
                $btn = '<a href="' . route('sr.show', ['sr' => $sr->id]) . '" class="btn btn-primary btn-sm mb-1" title="View"><i class="fa fa-eye"></i></a> ';
                if (auth()->user()->hasPermissionTo('sr_edit')) {
                    $btn .= '<a href="' . route('employees.edit', ['employee' => $sr->id]) . '" class="btn btn-info btn-sm mb-1" title="Edit Employee"><i class="fa fa-edit"></i></a> ';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('image', function (Employee $sr) {
                $url = asset('img/avatar.png');
                if ($sr->photo) {
                    $url = asset($sr->photo);
                }
                return '<img src="' . $url . '" width="50px" height="50px" class="img-circle" style="border: 1px solid #ddd;">';
            })
            ->addColumn('address', function (Employee $sr) {
                return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="' . $sr->id . '"><i class="fa fa-map-marker-alt"></i> View Address</button>';
            })
            ->rawColumns(['image', 'action', 'address'])
            ->toJson();
    }



    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_create'), 403, 'Unauthorized');
        return redirect()->route('employees.create');
    }

    public function show(Employee $sr)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr'), 403, 'Unauthorized');
        if (!$sr->isSR()) {
            abort(403, 'Invalid SR');
        }
        $sr->load('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava', 'sisterConcern');
        
        $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        
        $assignment = TaskAssignment::where('employee_id', $sr->id)
            ->where('date', $currentMonthStart)
            ->where('target_role', 'SR')
            ->first();

        $tasks = collect();
        if ($assignment) {
            $tasks = TaskItem::where('task_assignment_id', $assignment->id)
                ->with('type')
                ->get();

            foreach ($tasks as $task) {
                $task->actual_value = $this->calculateActualValue($sr->id, $task->type->slug, $currentMonthStart);
            }
        }

        return view('sr_management.sr.show', compact('sr', 'tasks'));
    }



    public function calculateActualValue($srId, $slug, $month)
    {
        $monthDate = Carbon::parse($month);
        $start = $monthDate->copy()->startOfMonth()->format('Y-m-d');
        $end = $monthDate->copy()->endOfMonth()->format('Y-m-d');

        switch ($slug) {
            case 'sales_target':
                return DB::table('sale_orders')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->sum('total') ?? 0;

            case 'order_count':
                return DB::table('sale_orders')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'payment_collection':
                return DB::table('sale_payments')
                    ->where('sr_id', $srId)
                    ->where('type', 1)
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount') ?? 0;

            case 'visit_count':
                return DB::table('sr_visits')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'youtube_subscriber':
                return DB::table('sr_social_engagements')
                    ->where('sr_id', $srId)
                    ->where('platform', 'youtube')
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'facebook_follower':
                return DB::table('sr_social_engagements')
                    ->where('sr_id', $srId)
                    ->where('platform', 'facebook')
                    ->whereBetween('date', [$start, $end])
                    ->count();

            default:
                return 0;
        }
    }
}
