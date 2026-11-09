<?php

namespace App\Http\Controllers\ManagerManagement;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\ManagerManagement\ManagerAssignment;
use App\Enumeration\RoleType;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function areaManagerHistoryReport(Request $request)
    {
        $assignments = [];
        $managers = Employee::areaManagers()
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        if ($request->manager_id != '') {
            $assignments = ManagerAssignment::with(['sisterConcern', 'manager'])
                ->where('manager_id', $request->manager_id)
                ->orderBy('assigned_date', 'desc')
                ->get();
        }

        $title = 'Area Manager Assignment History Report';
        return view('manager_management.reports.manager_history', compact('assignments', 'managers', 'title'));
    }

    public function branchManagerHistoryReport(Request $request)
    {
        $assignments = [];
        $managers = Employee::branchManagers()
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        if ($request->manager_id != '') {
            $assignments = ManagerAssignment::with(['sisterConcern', 'manager'])
                ->where('manager_id', $request->manager_id)
                ->orderBy('assigned_date', 'desc')
                ->get();
        }

        $title = 'Branch Manager Assignment History Report';
        return view('manager_management.reports.manager_history', compact('assignments', 'managers', 'title'));
    }

    public function areaManagerReport(Request $request)
    {
        $query = Employee::areaManagers()
            ->where('sister_concern_id', auth()->user()->activeConcernId());
            
        $managers = $query->orderBy('name')->get();
        return view('manager_management.reports.area_manager_report', compact('managers'));
    }

    public function branchManagerReport(Request $request)
    {
        $query = Employee::branchManagers()
            ->where('sister_concern_id', auth()->user()->activeConcernId());
            
        $managers = $query->orderBy('name')->get();
        return view('manager_management.reports.branch_manager_report', compact('managers'));
    }
}
