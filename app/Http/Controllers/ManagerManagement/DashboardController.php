<?php

namespace App\Http\Controllers\ManagerManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Enumeration\RoleType;
use App\Models\ManagerManagement\ManagerAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        $totalAreaManagers = Employee::areaManagers()->where('sister_concern_id', $sisterConcernId)->count();
        $totalBranchManagers = Employee::branchManagers()->where('sister_concern_id', $sisterConcernId)->count();
        
        $totalAreaManagerAssignments = ManagerAssignment::where('sister_concern_id', $sisterConcernId)
                                            ->where('manager_type', RoleType::$AREA_MANAGER)
                                            ->count();

        $totalBranchManagerAssignments = ManagerAssignment::where('sister_concern_id', $sisterConcernId)
                                            ->where('manager_type', RoleType::$BRANCH_MANAGER)
                                            ->count();

        return view('manager_management.dashboard', compact(
            'totalAreaManagers',
            'totalBranchManagers',
            'totalAreaManagerAssignments',
            'totalBranchManagerAssignments'
        ));
    }
}
