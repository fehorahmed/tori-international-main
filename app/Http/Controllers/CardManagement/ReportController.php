<?php

namespace App\Http\Controllers\CardManagement;

use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\Voucher;
use Illuminate\Http\Request;
use App\Models\CardManagement\Card;
use App\Models\CardManagement\CardPrintLog;
use App\Models\Supplier;
use App\Models\Hrm\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ==========================================
    // COLLECTION REPORTS
    // ==========================================

    public function collectionReport(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_report'), 403);
        $pageTitle = 'Card Payment Collection Report';

        $query = Card::with(['cardable', 'creator'])
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('cardable_type', Supplier::class)
            ->where('payment_status', 'Received');

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('updated_at', [$start, $end]);
        }

        $cards = $query->orderBy('updated_at', 'DESC')->get();

        $totalAmount = $cards->sum('amount');

        return view('card_management.reports.collection.index', compact('pageTitle', 'cards', 'totalAmount'));
    }

    // ==========================================
    // MEMBER REPORTS
    // ==========================================

    public function memberStatusSummary()
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_report'), 403);
        $pageTitle = 'Member Card Status Summary';
        
        $statusCounts = Card::where('cardable_type', Supplier::class)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statuses = ['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'];
        $summary = [];
        $total = 0;
        foreach ($statuses as $status) {
            $count = $statusCounts[$status] ?? 0;
            $summary[$status] = $count;
            $total += $count;
        }

        return view('card_management.reports.member.status_summary', compact('pageTitle', 'summary', 'total'));
    }

    public function memberPrintHistory(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_report'), 403);
        $pageTitle = 'Member Card Print History';

        $query = CardPrintLog::with(['card.cardable', 'printer'])
            ->where('card_print_logs.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('card', function($q) {
                $q->where('cardable_type', Supplier::class);
            });

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('printed_at', [$start, $end]);
        }

        $logs = $query->orderBy('id', 'DESC')->get();

        return view('card_management.reports.member.print_history', compact('pageTitle', 'logs'));
    }

    public function memberLostDamaged(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_report'), 403);
        $pageTitle = 'Lost/Damaged Member Cards';

        $query = Card::where('cardable_type', Supplier::class)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereIn('status', ['Lost', 'Damaged'])
            ->with(['cardable']);

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('updated_at', [$start, $end]);
        }

        $cards = $query->orderBy('updated_at', 'DESC')->get();

        return view('card_management.reports.member.lost_damaged', compact('pageTitle', 'cards'));
    }


    // ==========================================
    // EMPLOYEE REPORTS
    // ==========================================

    public function employeeStatusSummary()
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_report'), 403);
        $pageTitle = 'Employee Card Status Summary';
        
        $statusCounts = Card::where('cardable_type', Employee::class)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statuses = ['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'];
        $summary = [];
        $total = 0;
        foreach ($statuses as $status) {
            $count = $statusCounts[$status] ?? 0;
            $summary[$status] = $count;
            $total += $count;
        }

        return view('card_management.reports.employee.status_summary', compact('pageTitle', 'summary', 'total'));
    }

    public function employeePrintHistory(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_report'), 403);
        $pageTitle = 'Employee Card Print History';

        $query = CardPrintLog::with(['card.cardable', 'printer'])
            ->where('card_print_logs.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('card', function($q) {
                $q->where('cardable_type', Employee::class);
            });

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('printed_at', [$start, $end]);
        }

        $logs = $query->orderBy('id', 'DESC')->get();

        return view('card_management.reports.employee.print_history', compact('pageTitle', 'logs'));
    }

    public function employeeLostDamaged(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_report'), 403);
        $pageTitle = 'Lost/Damaged Employee Cards';

        $query = Card::where('cardable_type', Employee::class)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereIn('status', ['Lost', 'Damaged'])
            ->with(['cardable', 'cardable.department']);

        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('updated_at', [$start, $end]);
        }

        $cards = $query->orderBy('updated_at', 'DESC')->get();

        return view('card_management.reports.employee.lost_damaged', compact('pageTitle', 'cards'));
    }
}
