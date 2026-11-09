<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\EmployeeAdvance;
use App\Models\Hrm\Employee;
use App\Models\Account\AccountHead;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Payroll\Contracts\EmployeeAdvanceRepositoryInterface;
use App\Http\Requests\Payroll\StoreEmployeeAdvanceRequest;
use App\Services\Payroll\EmployeeAdvanceService;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;

class EmployeeAdvanceController extends Controller
{
    protected $advanceService;
    protected $advanceRepository;

    public function __construct(EmployeeAdvanceService $advanceService, EmployeeAdvanceRepositoryInterface $advanceRepository)
    {
        $this->advanceService = $advanceService;
        $this->advanceRepository = $advanceRepository;
    }

    public function index()
    {
        return view('payroll.employee_advance.index');
    }

    public function dataTable()
    {
        $query = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->withAdvanceBalance();

        return DataTables::eloquent($query)
            ->addColumn('total_amount', function (Employee $employee) {
                return $employee->total_debit ?? 0;
            })
            ->addColumn('total_paid', function (Employee $employee) {
                return $employee->total_credit ?? 0;
            })
            ->addColumn('balance', function (Employee $employee) {
                $balance = ($employee->total_debit ?? 0) - ($employee->total_credit ?? 0);
                return '<span class="text-danger font-weight-bold">' . number_format($balance, 2) . '</span>';
            })
            ->editColumn('designation', function (Employee $employee) {
                return $employee->designation->name ?? 'N/A';
            })
            ->rawColumns(['balance'])
            ->toJson();
    }

    public function requests(Request $request)
    {
        // Get Accounts with Payment Mode > 0 (Cash/Bank)
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
        ->where('payment_mode', '>', 0)
        ->where('status', 1)
        ->get();

        $employees = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->get();

        return view('payroll.employee_advance.requests', compact('paymentModes', 'employees'));
    }

    public function requestsDataTable(Request $request)
    {
        $query = $this->advanceRepository->datatable(auth()->user()->activeConcernId())
                      ->with(['createdBy', 'approvedBy']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', date('Y-m-d', strtotime($request->date)));
        }

        return DataTables::eloquent($query)
            ->addColumn('action', function (EmployeeAdvance $advance) {
                $btn = '';
                if ($advance->status === 'Pending') {
                    $btn .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $advance->id . '" data-amount="' . $advance->amount . '" data-employee="' . ($advance->employee->name ?? '') . '"><i class="fa fa-check"></i> Approve</button> ';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $advance->id . '"><i class="fa fa-times"></i> Reject</button>';
                } else {
                    $btn .= '<span class="badge badge-' . ($advance->status == 'Approved' || $advance->status == 'Paid' ? 'success' : 'danger') . '">' . $advance->status . '</span>';
                }
                return $btn;
            })
            ->addColumn('audit_info', function (EmployeeAdvance $advance) {
                $created = $advance->createdBy->name ?? 'Unknown';
                $approved = $advance->approvedBy->name ?? 'Not Approved';
                return "<div><small><b>Created By:</b> $created</small><br><small><b>Approved By:</b> $approved</small></div>";
            })
            ->editColumn('status', function (EmployeeAdvance $advance) {
                $class = $advance->status === 'Pending' ? 'warning' : ($advance->status === 'Rejected' ? 'danger' : 'success');
                return '<span class="badge badge-' . $class . '">' . $advance->status . '</span>';
            })
            ->editColumn('employee_id', function (EmployeeAdvance $advance) {
                return $advance->employee->name ?? '';
            })
            ->editColumn('date', function (EmployeeAdvance $advance) {
                return date('d-m-Y', strtotime($advance->date));
            })
            ->rawColumns(['action', 'status', 'audit_info'])
            ->toJson();
    }

    public function create()
    {
        $employees = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->get();
            
        return view('payroll.employee_advance.create', compact('employees'));
    }

    public function store(StoreEmployeeAdvanceRequest $request)
    {
        $this->advanceRepository->create([
            'employee_id' => $request->employee_id,
            'sister_concern_id' => auth()->user()->activeConcernId(),
            'date' => date('Y-m-d', strtotime($request->date)),
            'amount' => $request->amount,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('employee-advances.index')->with('success', 'Employee Advance Request Created Successfully');
    }

    public function approve(Request $request, EmployeeAdvance $employee_advance)
    {
        if ($employee_advance->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.']);
        }

        $request->validate([
            'payment_mode' => 'required|string',
        ]);

        // Payment mode usually comes as id|payment_mode
        $parts = explode('|', $request->payment_mode);
        $paymentAccountHeadId = $parts[0] ?? null;

        if (!$paymentAccountHeadId) {
            return response()->json(['success' => false, 'message' => 'Invalid payment mode.']);
        }

        $result = $this->advanceService->approve($employee_advance, $paymentAccountHeadId);

        return response()->json($result);
    }

    public function reject(EmployeeAdvance $employee_advance)
    {
        if ($employee_advance->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Transaction is not pending.']);
        }

        $this->advanceRepository->update($employee_advance->id, ['status' => 'Rejected']);
        return response()->json(['success' => true, 'message' => 'Transaction rejected.']);
    }
}
