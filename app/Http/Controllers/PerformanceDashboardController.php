<?php

namespace App\Http\Controllers;

use App\Models\Sales\TaskType;
use Illuminate\Http\Request;
use App\Models\Hrm\Employee;
use App\Models\Sales\TaskAssignment;
use App\Enumeration\RoleType;
use Carbon\Carbon;

class PerformanceDashboardController extends Controller
{
    public function srPerformance(Request $request)
    {
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();
            
        $timeframe = $request->timeframe ?? 'daily';
        $monthStr = $request->month ?? now()->format('m-Y');
        
        $monthDate = Carbon::createFromFormat('d-m-Y', '01-' . $monthStr)->startOfMonth();

        if ($timeframe == 'daily') {
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            $targetMonthDate = now()->startOfMonth()->format('Y-m-d');
            $monthStr = now()->format('m-Y');
            $reportTitle = "Daily Target (" . now()->format('d M, Y') . ")";
        } elseif ($timeframe == 'weekly') {
            $startDate = now()->startOfWeek()->format('Y-m-d');
            $endDate = now()->endOfWeek()->format('Y-m-d');
            $targetMonthDate = now()->startOfMonth()->format('Y-m-d');
            $monthStr = now()->format('m-Y');
            $reportTitle = "Weekly Target (" . now()->startOfWeek()->format('d M') . " - " . now()->endOfWeek()->format('d M, Y') . ")";
        } elseif ($timeframe == 'monthly') {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            $targetMonthDate = now()->startOfMonth()->format('Y-m-d');
            $monthStr = now()->format('m-Y');
            $reportTitle = "Monthly Target (" . now()->format('F, Y') . ")";
        } else { // custom
            $startDate = $monthDate->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $monthDate->copy()->endOfMonth()->format('Y-m-d');
            $targetMonthDate = $monthDate->copy()->format('Y-m-d');
            $reportTitle = "Custom Target (" . $monthDate->copy()->format('F, Y') . ")";
        }

        $selectedSrId = $request->sr_id ?? '';

        // Fetch all task types
        $allTaskTypes = TaskType::all();
        
        $kpiBreakdowns = [];
        
        // Initialize all task types with 0 target
        foreach ($allTaskTypes as $type) {
            $kpiBreakdowns[$type->id] = [
                'name' => $type->name,
                'slug' => $type->slug,
                'target' => 0,
                'achieved' => 0,
                'is_amount' => ($type->unit === 'Amount') 
            ];
        }

        $query = TaskAssignment::with('employee', 'items.type')
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('target_role', RoleType::$SR)
            ->where('date', $targetMonthDate);

        if ($selectedSrId != '') {
            $query->where('employee_id', $selectedSrId);
        }

        $assignments = $query->get();

        $targetMultiplier = $this->getTargetMultiplier($timeframe);

        foreach ($assignments as $assignment) {
            foreach ($assignment->items as $item) {
                $typeId = $item->task_type_id;
                if (isset($kpiBreakdowns[$typeId])) {
                    $proportionalTarget = $item->target_value * $targetMultiplier;
                    
                    if ($kpiBreakdowns[$typeId]['is_amount']) {
                        $kpiBreakdowns[$typeId]['target'] += round($proportionalTarget, 2);
                    } else {
                        $kpiBreakdowns[$typeId]['target'] += round($proportionalTarget);
                    }
                }
            }
        }
        
        // Calculate actuals for all types
        // If no SR is selected, should we calculate for all SRs?
        // The previous logic calculated actuals per assignment employee.
        // If $selectedSrId is empty, calculating actuals for "all SRs" is complex using the current switch.
        // Wait, calculateTimeframeActualValue takes $srId. If $selectedSrId is empty, we need to sum for all SRs in the branch.
        $srIds = $selectedSrId != '' ? [$selectedSrId] : $srs->pluck('id')->toArray();
        
        foreach ($kpiBreakdowns as $typeId => &$kpi) {
            $totalActual = 0;
            foreach ($srIds as $srId) {
                $totalActual += $this->calculateTimeframeActualValue($srId, $kpi['slug'], $startDate, $endDate);
            }
            $kpi['achieved'] = $totalActual;
        }

        $recentVisits = \App\Models\SrManagement\SrVisit::with(['sr', 'supplier'])
            ->whereIn('sr_id', $srIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->take(20) // show up to 20 recent visits
            ->get();

        return view('performance_dashboard.sr', compact(
            'srs', 'monthStr', 'selectedSrId', 'timeframe', 'kpiBreakdowns', 'recentVisits', 'reportTitle'
        ));
    }

    private function getTargetMultiplier($timeframe)
    {
        if ($timeframe == 'daily') {
            return 1 / 26;
        } elseif ($timeframe == 'weekly') {
            return 6 / 26;
        }
        
        return 1; // Monthly or Custom defaults to 1
    }

    private function calculateTimeframeActualValue($srId, $slug, $start, $end)
    {
        switch ($slug) {
            case 'sales_target':
                return \Illuminate\Support\Facades\DB::table('sale_orders')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->sum('total') ?? 0;

            case 'order_count':
                return \Illuminate\Support\Facades\DB::table('sale_orders')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'payment_collection':
                return \Illuminate\Support\Facades\DB::table('sale_payments')
                    ->where('sr_id', $srId)
                    ->where('type', 1)
                    ->whereBetween('date', [$start, $end])
                    ->sum('amount') ?? 0;

            case 'visit_count':
                return \Illuminate\Support\Facades\DB::table('sr_visits')
                    ->where('sr_id', $srId)
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'youtube_subscriber':
                return \Illuminate\Support\Facades\DB::table('sr_social_engagements')
                    ->where('sr_id', $srId)
                    ->where('platform', 'youtube')
                    ->whereBetween('date', [$start, $end])
                    ->count();

            case 'facebook_follower':
                return \Illuminate\Support\Facades\DB::table('sr_social_engagements')
                    ->where('sr_id', $srId)
                    ->where('platform', 'facebook')
                    ->whereBetween('date', [$start, $end])
                    ->count();

            default:
                return 0;
        }
    }

    public function branchManagerPerformance(Request $request)
    {
        return view('performance_dashboard.bm');
    }

    public function areaManagerPerformance(Request $request)
    {
        return view('performance_dashboard.am');
    }
}
