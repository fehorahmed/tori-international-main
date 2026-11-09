<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $concernId = auth()->user()->activeConcernId();

        // Key Metrics
        $totalEmployees = Employee::where('sister_concern_id', $concernId)->where('status', 1)->count();
        $totalDepartments = Department::count();
        $totalDesignations = Designation::count();
        
        // Employees on leave today
        $today = Carbon::today()->format('Y-m-d');
        $employeesOnLeave = Leave::whereHas('employee', function ($q) use ($concernId) {
                $q->where('sister_concern_id', $concernId);
            })
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('status', 1) // Approved
            ->count();

        // Recent Employees
        $recentEmployees = Employee::with(['department', 'designation'])
            ->where('sister_concern_id', $concernId)
            ->whereDate('created_at', auth()->user()->business_date)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $typeName = 'HRM Dashboard';

        return view('hrm.dashboard', compact(
            'typeName',
            'totalEmployees',
            'totalDepartments',
            'totalDesignations',
            'employeesOnLeave',
            'recentEmployees'
        ));
    }
}
