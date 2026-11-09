<?php

namespace App\Http\Controllers\BranchManagement;

use App\Http\Controllers\Controller;
use App\Enumeration\RoleType;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function branchReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_report')) {
            abort(403, 'Unauthorized');
        }

        $query = SisterConcern::query();
        if(auth()->user()->role == RoleType::$AREA_MANAGER) {
            $query->where('area_manager_id', auth()->user()->employee_id); 
        }

        $branches = $query->with(['areaManager', 'branchManager'])->get();
        $typeName = 'Branch';

        return view('branch_management.reports.sister_concern', compact('branches', 'typeName'));
    }

    public function employeeReport(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('branch_report')) {
            abort(403, 'Unauthorized');
        }

        $query = Employee::with('sisterConcern', 'designation', 'department')->orderBy('name');

        if ($request->sister_concern_id != '') {
            $query->where('sister_concern_id', $request->sister_concern_id);
        }

        if ($request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->designation_id != '') {
            $query->where('designation_id', $request->designation_id);
        }

        if(auth()->user()->role == RoleType::$AREA_MANAGER) {
            $branchIds = SisterConcern::where('area_manager_id', auth()->user()->employee_id)->pluck('id');
            $query->whereIn('sister_concern_id', $branchIds);
        }

        $employees = $query->get();

        $queryBranches = SisterConcern::orderBy('name');
        if(auth()->user()->role == RoleType::$AREA_MANAGER) {
            $queryBranches->where('area_manager_id', auth()->user()->employee_id);
        }
        $sisterConcerns = $queryBranches->get();

        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        if ($request->has('search')) {
            return view('branch_management.reports.employee_report', compact('employees', 'sisterConcerns', 'departments', 'designations'));
        }

        return view('branch_management.reports.employee', compact('sisterConcerns', 'departments', 'designations'));
    }
}
