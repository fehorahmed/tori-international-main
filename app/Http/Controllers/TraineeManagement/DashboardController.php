<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TraineeManagement\TraineeVisit;
use App\Models\Hrm\Employee;
use App\Enumeration\RoleType;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        $totalTrainees = Employee::where('sister_concern_id', $sisterConcernId)
            ->trainees()
            ->count();

        $totalVisits = TraineeVisit::whereHas('trainee', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            })->count();
            
        $todayVisits = TraineeVisit::whereHas('trainee', function($q) use ($sisterConcernId) {
                $q->where('sister_concern_id', $sisterConcernId);
            })->whereDate('date', today())->count();

        return view('trainee_management.dashboard', compact('totalTrainees', 'totalVisits', 'todayVisits'));
    }
}
