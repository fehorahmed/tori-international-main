<?php

namespace App\Http\Controllers\SrManagement;

use App\Http\Controllers\Controller;
use App\Models\Crm\Lead;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Models\SrManagement\SrVisit;

use App\Models\Sales\TaskAssignment;
use App\Enumeration\RoleType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function srPerformanceReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_performance_report'), 403, 'Unauthorized');
        $assignments = [];
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();

        if ($request->date != '') {
            $dateStr = str_replace('/', '-', $request->date);
            // Parse as 1st of the selected month
            $date = Carbon::parse('01-' . $dateStr)->startOfMonth()->format('Y-m-d');

            $query = TaskAssignment::with('employee', 'items.type')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('target_role', RoleType::$SR)
                ->where('date', $date);

            if ($request->sr_id != '') {
                $query->where('employee_id', $request->sr_id);
            }

            $assignments = $query->get();

            // Calculate actuals for each assignment
            foreach ($assignments as $assignment) {
                foreach ($assignment->items as $item) {
                    $item->actual_value = app(SrController::class)->calculateActualValue($assignment->employee_id, $item->type->slug, $assignment->date);
                }
            }
        }

        return view('sr_management.reports.sr_performance', compact('assignments', 'srs'));
    }

    public function srPerformanceSummary(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_performance_summary_report'), 403, 'Unauthorized');
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();

        $performanceData = [];
        $assignment = null;

        if ($request->sr_id != '' && $request->date != '' && $request->start_date != '' && $request->end_date != '') {
            $srId = $request->sr_id;
            $monthStr = str_replace('/', '-', $request->date);
            $monthDate = Carbon::parse('01-' . $monthStr)->startOfMonth()->format('Y-m-d');

            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $assignment = TaskAssignment::with('items.type')
                ->where('employee_id', $srId)
                ->where('target_role', RoleType::$SR)
                ->where('date', $monthDate)
                ->first();

            if ($assignment) {
                // Get all daily data
                $sales = DB::table('sale_orders')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('date', DB::raw('SUM(total) as total_sales'), DB::raw('COUNT(id) as order_count'))
                    ->groupBy('date')
                    ->get()
                    ->keyBy('date');

                $payments = DB::table('sale_payments')
                    ->where('sr_id', $srId)
                    ->where('type',1)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('date', DB::raw('SUM(amount) as total_payment'))
                    ->groupBy('date')
                    ->get()
                    ->keyBy('date');

                $visits = DB::table('sr_visits')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->select('date', DB::raw('COUNT(id) as visit_count'))
                    ->groupBy('date')
                    ->get()
                    ->keyBy('date');

                // Generate date range
                $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $d = $date->format('Y-m-d');
                    $performanceData[$d] = [
                        'sales' => $sales[$d]->total_sales ?? 0,
                        'orders' => $sales[$d]->order_count ?? 0,
                        'payments' => $payments[$d]->total_payment ?? 0,
                        'visits' => $visits[$d]->visit_count ?? 0,
                    ];
                }
            }
        }

        return view('sr_management.reports.sr_performance_summary', compact('srs', 'performanceData', 'assignment'));
    }

    public function srTasksReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_tasks_report'), 403, 'Unauthorized');
        $assignments = [];
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();

        if ($request->date != '') {
            $dateStr = str_replace('/', '-', $request->date);
            $date = Carbon::parse('01-' . $dateStr)->startOfMonth()->format('Y-m-d');

            $query = TaskAssignment::with('employee', 'items.type')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('target_role', 'SR')
                ->where('date', $date);

            if ($request->sr_id != '') {
                $query->where('employee_id', $request->sr_id);
            }

            $assignments = $query->get();
        }

        return view('sr_management.reports.sr_tasks', compact('assignments', 'srs'));
    }

    public function srVisitsReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_visits_report'), 403, 'Unauthorized');
        $visits = [];
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();
        
        if ($request->start_date != '' && $request->end_date != '') {
            $query = SrVisit::with(['sr', 'supplier'])
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ]);

            if ($request->sr_id != '') {
                $query->where('sr_id', $request->sr_id);
            }
            


            $visits = $query->orderBy('date', 'desc')->get();
        }

        return view('sr_management.reports.sr_visits', compact('visits', 'srs'));
    }

    public function srSocialEngagementsReport(Request $request)
    {
        $engagements = [];
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();

        if ($request->start_date != '' && $request->end_date != '') {
            $query = \App\Models\SrManagement\SrSocialEngagement::with(['sr', 'user'])
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ]);

            if ($request->sr_id != '') {
                $query->where('sr_id', $request->sr_id);
            }

            if ($request->platform != '') {
                $query->where('platform', $request->platform);
            }

            $engagements = $query->orderBy('date', 'desc')->get();
        }

        return view('sr_management.reports.social_engagements', compact('engagements', 'srs'));
    }

    public function srLeadsReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('sr_leads_report'), 403, 'Unauthorized');
        $leads = [];
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();


        if ($request->start_date != '' && $request->end_date != '') {
            $query = Lead::with(['sr', 'crmClient', 'user'])
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->whereNotNull('sr_id')
                ->whereBetween('date', [
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ]);

            if ($request->sr_id != '') {
                $query->where('sr_id', $request->sr_id);
            }


            $leads = $query->orderBy('date', 'desc')->get();
        }

        return view('sr_management.reports.sr_leads', compact('leads', 'srs'));
    }
}
