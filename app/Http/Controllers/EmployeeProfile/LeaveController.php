<?php

namespace App\Http\Controllers\EmployeeProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;

class LeaveController extends Controller
{
    public function index()
    {
        $pageTitle = 'Attendance & Leave';
        $user = auth()->user();
        $employee = $user->employee_id ? Employee::find($user->employee_id) : null;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'You do not have an associated Employee Profile.');
        }

        return view('employee_profile.leave.index', compact('pageTitle', 'employee'));
    }
}
