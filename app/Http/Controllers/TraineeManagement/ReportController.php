<?php

namespace App\Http\Controllers\TraineeManagement;

use App\Http\Controllers\Controller;
use App\Enumeration\RoleType;
use App\Models\Hrm\Employee;
use App\Models\Sales\TaskAssignment;
use App\Models\TraineeManagement\TraineeVisit;
use App\Models\TraineeManagement\TraineeVisitType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function traineeVisitsReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_visits_report'), 403, 'Unauthorized');
        $visits = [];
        $trainees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->trainees()
            ->get();
        
        $visitTypes = TraineeVisitType::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->get();

        if ($request->start_date != '' && $request->end_date != '') {
            $query = TraineeVisit::with(['trainee', 'visitType', 'visitClient'])
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ]);

            if ($request->trainee_id != '') {
                $query->where('trainee_id', $request->trainee_id);
            }

            if ($request->trainee_visit_type_id != '') {
                $query->where('trainee_visit_type_id', $request->trainee_visit_type_id);
            }

            $visits = $query->orderBy('date', 'desc')->get();
        }

        return view('trainee_management.reports.trainee_visits', compact('visits', 'trainees', 'visitTypes'));
    }

    public function traineePerformanceReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_performance_report'), 403, 'Unauthorized');
        $assignments = [];
        $trainees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->trainees()
            ->get();

        if ($request->date != '') {
            $dateStr = str_replace('/', '-', $request->date);
            $date = Carbon::parse('01-' . $dateStr)->startOfMonth()->format('Y-m-d');

            $query = TaskAssignment::with('employee', 'items.type')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('target_role', 'Trainee')
                ->where('date', $date);

            if ($request->trainee_id != '') {
                $query->where('employee_id', $request->trainee_id);
            }

            $assignments = $query->get();

            // Calculate actuals
            foreach ($assignments as $assignment) {
                foreach ($assignment->items as $item) {
                    $item->actual_value = app(\App\Http\Controllers\TraineeManagement\TraineeTaskController::class)->calculateActualValue($assignment->employee_id, $item->type->slug, $assignment->date);
                }
            }
        }

        return view('trainee_management.reports.trainee_performance', compact('assignments', 'trainees'));
    }

    public function traineePerformanceSummary(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('trainee_performance_summary_report'), 403, 'Unauthorized');
        $trainees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->trainees()
            ->get();

        $performanceData = [];
        $assignment = null;

        if ($request->trainee_id != '' && $request->date != '' && $request->start_date != '' && $request->end_date != '') {
            $traineeId = $request->trainee_id;
            $monthStr = str_replace('/', '-', $request->date);
            $monthDate = Carbon::parse('01-' . $monthStr)->startOfMonth()->format('Y-m-d');

            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $assignment = TaskAssignment::with('items.type')
                ->where('employee_id', $traineeId)
                ->where('target_role', 'Trainee')
                ->where('date', $monthDate)
                ->first();

            if ($assignment) {
                $visits = DB::table('trainee_visits')
                    ->where('trainee_id', $traineeId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('date', DB::raw('COUNT(id) as visit_count'))
                    ->groupBy('date')
                    ->get()
                    ->keyBy('date');
                
                $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $d = $date->format('Y-m-d');
                    $performanceData[$d] = [
                        'sales' => 0,
                        'payment' => 0,
                        'order_count' => 0,
                        'visit_count' => $visits[$d]->visit_count ?? 0,
                    ];
                }
            }
        }

        return view('trainee_management.reports.trainee_performance_summary', compact('performanceData', 'trainees', 'assignment'));
    }
}
