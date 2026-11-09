<?php

namespace App\Http\Controllers\IncentiveManagement;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\IncentiveManagement\IncentiveLedger;
use App\Models\User;
use App\Services\IncentiveManagement\IncentiveCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class IncentivePaymentController extends Controller
{
    public function __construct(
        protected IncentiveCalculationService $service
    ) {}

    // ─── Payment List ─────────────────────────────────────────────

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);

        $scId      = auth()->user()->activeConcernId();
        $pageTitle = 'Employee Incentive';

        // Cash/Bank account heads
        $accountHeads = AccountHead::whereHas('accountGroup', fn($q) =>
            $q->where('name', 'LIKE', '%Cash%')->orWhere('name', 'LIKE', '%Bank%')
        )->where('sister_concern_id', $scId)->get();

        return view('incentive_management.payments.index', compact(
            'pageTitle', 'accountHeads'
        ));
    }

    // ─── Store Payment ────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment_create'), 403);

        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'amount'          => 'required|numeric|min:1',
            'account_head_id' => 'required|exists:account_heads,id',
            'payment_method'  => 'required|in:cash,bank_transfer,mobile_banking',
            'payment_date'    => 'required|date',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date',
            'role_type'       => 'nullable|string',
        ]);

        try {
            $this->service->processPayment($request->only([
                'employee_id', 'amount', 'account_head_id',
                'payment_method', 'payment_date', 'reference_no', 'note',
                'start_date', 'end_date', 'role_type'
            ]));

            return redirect()->route('incentive-payments.index')
                ->with('success', 'ইনসেনটিভ পেমেন্ট সফলভাবে সম্পন্ন হয়েছে!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ─── Payment History DataTable ────────────────────────────────

    public function dataTable(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);

        $scId  = auth()->user()->activeConcernId();
        
        $startDate = $request->start_date ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d') . ' 00:00:00' : null;
        $endDate   = $request->end_date ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d') . ' 23:59:59' : null;

        $query = IncentiveLedger::with(['employee.designation'])
            ->where('sister_concern_id', $scId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->role) {
            $query->where('role_type', $request->role);
        }

        // Group by employee to get the summary
        $query->selectRaw('
                employee_id,
                SUM(CASE WHEN type = "earned" THEN amount ELSE 0 END) as total_earned,
                SUM(CASE WHEN type = "paid" THEN ABS(amount) ELSE 0 END) as total_paid
            ')
            ->groupBy('employee_id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('employee_name', fn($p) =>
                ($p->employee->name ?? '') . '<br><small class="text-muted">' . ($p->employee->id_no ?? '') . ' | ' . ($p->employee->designation->name ?? 'N/A') . '</small>'
            )
            ->addColumn('total_earned', fn($p) =>
                '৳' . number_format($p->total_earned, 2)
            )
            ->addColumn('total_paid', fn($p) =>
                '৳' . number_format($p->total_paid, 2)
            )
            ->addColumn('due', fn($p) =>
                '৳' . number_format($p->total_earned - $p->total_paid, 2)
            )
            ->addColumn('action', function($p) {
                $due = round($p->total_earned - $p->total_paid, 2);
                $ledgerUrl = route('incentive-payments.ledger-page', $p->employee_id);
                $ledgerBtn = '<a href="'.$ledgerUrl.'" class="btn btn-sm btn-info"><i class="fas fa-list"></i> Ledger</a>';
                
                if ($due > 0) {
                    return '<button class="btn btn-sm btn-success pay-btn" data-emp="'.$p->employee_id.'" data-due="'.$due.'"><i class="fas fa-money-bill"></i> Pay</button> ' . $ledgerBtn;
                }
                return $ledgerBtn;
            })
            ->rawColumns(['employee_name', 'action'])
            ->toJson();
    }

    // ─── Employee Pending Balance (AJAX) ──────────────────────────

    public function getEmployeeBalance(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $balance = IncentiveLedger::getBalance(
            $request->employee_id,
            auth()->user()->activeConcernId()
        );

        return response()->json([
            'balance'           => $balance,
            'balance_formatted' => '৳' . number_format($balance, 2),
        ]);
    }

    // ─── Employee Ledger Detail (AJAX) ───────────────────────────

    public function employeeLedger(Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);

        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $scId    = auth()->user()->activeConcernId();
        $ledgers = IncentiveLedger::where('employee_id', $request->employee_id)
            ->where('sister_concern_id', $scId)
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($l) => [
                'date'      => $l->created_at->format('d M Y'),
                'type'      => $l->type,
                'role_type' => $l->role_type,
                'amount'    => '৳' . number_format(abs($l->amount), 2),
                'sign'      => $l->amount >= 0 ? '+' : '-',
                'note'      => $l->note,
            ]);

        return response()->json(['ledger' => $ledgers]);
    }

    public function employeeLedgerPage($employeeId)
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);
        
        $employee = \App\Models\Hrm\Employee::findOrFail($employeeId);
        $pageTitle = "Incentive Ledger: " . $employee->name . " (" . $employee->id_no . ")";
        
        return view('incentive_management.payments.ledger', compact('pageTitle', 'employee'));
    }

    public function ledgerDatatable($employeeId, Request $request): JsonResponse
    {
        abort_if(!auth()->user()->hasPermissionTo('incentive_payment'), 403);
        
        $scId = auth()->user()->activeConcernId();
        
        $query = IncentiveLedger::where('employee_id', $employeeId)
            ->where('sister_concern_id', $scId)
            ->orderBy('id', 'desc');
            
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('date', fn($l) => $l->created_at->format('d M Y h:i A'))
            ->addColumn('type_badge', function($l) {
                if ($l->type === 'earned') return '<span class="badge badge-success">Earned</span>';
                if ($l->type === 'paid') return '<span class="badge badge-primary">Paid</span>';
                return '<span class="badge badge-warning">'.$l->type.'</span>';
            })
            ->addColumn('role_type_formatted', function($l) {
                return '<span class="text-capitalize">'.str_replace('_', ' ', $l->role_type).'</span>';
            })
            ->addColumn('amount_formatted', function($l) {
                $sign = $l->amount >= 0 ? '+' : '';
                $color = $l->amount >= 0 ? 'text-success' : 'text-danger';
                return '<span class="font-weight-bold '.$color.'">'.$sign . number_format($l->amount, 2).'</span>';
            })
            ->rawColumns(['type_badge', 'amount_formatted', 'role_type_formatted'])
            ->toJson();
    }
}
