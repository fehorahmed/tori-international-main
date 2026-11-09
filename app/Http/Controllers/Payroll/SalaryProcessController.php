<?php

namespace App\Http\Controllers\Payroll;

use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Hrm\AttendanceController;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\Payroll\SalaryProcess;
use App\Models\Payroll\SalaryProcessItem;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalaryProcessController extends Controller
{
    /**
     * Display a listing of the resource.A
     */
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('salary_process_lists')) {
            abort(403, 'Unauthorized');
        }
        return view('payroll.salary_process.index');
    }
    public function details(SalaryProcess $salary_process)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_items')) {
            abort(403, 'Unauthorized');
        }
        $salaryProcesses = SalaryProcess::get();
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        return view('payroll.salary_process.details', compact('salaryProcesses',
            'salary_process','employees', 'paymentModes'));
    }

    public function dataTable()
    {
        $query = SalaryProcess::with( 'salaryProcessItems');

        if (request()->has('year') && request('year') != '') {
            $query->where('year', request('year'));
        }
        if (request()->has('month') && request('month') != '') {
            $query->where('month', request('month'));
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (SalaryProcess $salary_process) {
                $btn = '';

                if (auth()->user()->hasPermissionTo('salary_process_item_delete')) {
                    $btn .= ' <a role="button" data-id="' . $salary_process->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                if ($salary_process->voucher) {
                    $btn .= ' <a target="_blank" href="' . route('voucher.details', ['voucher' => $salary_process->voucher->id, 'voucher_type' => $salary_process->voucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-info-circle"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('salary_process_items')) {
                    $btn .= ' <a href="' . route('salary-processes.details', ['salary_process' => $salary_process->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-info-circle"></i></a>';
                }
                return dropdownMenuContainer($btn);

            })

            ->editColumn('month', function (SalaryProcess $salary_process) {
                return numberToMonthName($salary_process->month);
            })
            ->addColumn('gross', function (SalaryProcess $salary_process) {
                return number_format($salary_process->salaryProcessItems->sum('gross') ?? 0, 2);
            })
            ->addColumn('total_deduction', function (SalaryProcess $salary_process) {
                return number_format($salary_process->salaryProcessItems->sum('total_deduction') ?? 0, 2);
            })
            ->addColumn('net_pay', function (SalaryProcess $salary_process) {
                return number_format($salary_process->salaryProcessItems->sum('net_pay') ?? 0, 2);
            })
            ->rawColumns(['action'])
            ->toJson();
    }
    public function detailsDataTable()
    {
        $query = SalaryProcessItem::select('salary_process_items.*')->with('employee', 'salaryProcess',
            'voucher','jVoucher','employee.designation','employee.department');

        if (request()->has('salary_process') && request('salary_process') != ''){
            $query->where('salary_process_id', request('salary_process'));
        }
        if (request()->has('employee') && request('employee') != ''){
            $query->where('employee_id', request('employee'));
        }

        if ((request()->has('year') && request('year') != '') || (request()->has('month') && request('month') != '')) {
            $query->whereHas('salaryProcess', function ($query) {
                if (request()->has('year') && request('year') != '') {
                    $query->where('year', request('year'));
                }
                if (request()->has('month') && request('month') != '') {
                    $query->where('month', request('month'));
                }
            });
        }

        if (request()->has('employee') && request('employee') != '') {
            $query->where('employee_id', request('employee'));
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (SalaryProcessItem $salaryProcessItem) {
                $btn = '';
                if (auth()->user()->hasPermissionTo('salary_process_items')) {
                    $btn .= '<a href="' . route('salary-processes.item_view',['salary_process_item'=>$salaryProcessItem->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-view" title="View Payslip"><i class="fa fa-eye"></i></a> ';
                    $btn .= '<a role="button" data-url="' . route('salary-processes.item_history',['salary_process_item'=>$salaryProcessItem->id]) . '" class="btn btn-secondary bg-gradient-secondary btn-sm btn-history" title="View Audit History"><i class="fa fa-history"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('salary_process_item_edit') && !$salaryProcessItem->voucher_id) {
                    $btn .= '<a href="' . route('salary-processes.item_edit',['salary_process_item'=>$salaryProcessItem->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit" title="Edit Salary"><i class="fa fa-edit"></i></a> ';
                }
                if (auth()->user()->hasPermissionTo('salary_process_item_delete')) {
                    $btn .= ' <a role="button" data-id="' . $salaryProcessItem->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                if ($salaryProcessItem->voucher) {
                    $btn .= ' <a target="_blank" href="' . route('voucher.details', ['voucher' => $salaryProcessItem->voucher->id, 'voucher_type' => $salaryProcessItem->voucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit">PV <i class="fa fa-info-circle"></i></a>';
                }
                if ($salaryProcessItem->jVoucher) {
                    $btn .= ' <a target="_blank" href="' . route('voucher.details', ['voucher' => $salaryProcessItem->jVoucher->id, 'voucher_type' => $salaryProcessItem->jVoucher->voucher_type]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit">JV <i class="fa fa-info-circle"></i></a>';
                }

                return dropdownMenuContainer($btn);

            })
            ->addColumn('employee_name', function (SalaryProcessItem $salaryProcessItem) {
                $name = $salaryProcessItem->employee->name ?? '';
                $designation = $salaryProcessItem->employee->designation->name ?? '';
                $department = $salaryProcessItem->employee->department->name ?? '';
                return '<div><span class="font-weight-bold">' . $name . '</span><br>' .
                       '<small class="text-muted">' . $designation . '</small><br>' .
                       '<small class="text-muted">' . $department . '</small></div>';
            })
            ->addColumn('year', function (SalaryProcessItem $salaryProcessItem) {
                return $salaryProcessItem->salaryProcess->year;
            })
            ->addColumn('month', function (SalaryProcessItem $salaryProcessItem) {
                return numberToMonthName($salaryProcessItem->salaryProcess->month);
            })
            ->addColumn('process_date', function (SalaryProcessItem $salaryProcessItem) {
                return Carbon::parse($salaryProcessItem->salaryProcess->process_date)->format('d-m-Y');
            })
            ->addColumn('gross', function (SalaryProcessItem $salaryProcessItem) {
                return number_format($salaryProcessItem->gross, 2);
            })
            ->addColumn('total_deduction', function (SalaryProcessItem $salaryProcessItem) {
                return number_format($salaryProcessItem->total_deduction, 2);
            })
            ->addColumn('net_pay', function (SalaryProcessItem $salaryProcessItem) {
                return number_format($salaryProcessItem->net_pay, 2);
            })
            ->addColumn('checkbox', function (SalaryProcessItem $salaryProcessItem) {
                if ($salaryProcessItem->voucher_id) {
                    return '<span class="badge badge-success">Paid</span>';
                }
                return '<input type="checkbox" class="disburse-checkbox" value="'.$salaryProcessItem->id.'" data-netpay="'.$salaryProcessItem->net_pay.'">';
            })
            ->addColumn('status', function (SalaryProcessItem $salaryProcessItem) {
                if ($salaryProcessItem->voucher_id) {
                    return '<span class="badge badge-success">Paid</span>';
                }
                return '<span class="badge badge-warning">Pending</span>';
            })
            ->rawColumns(['action', 'checkbox', 'status', 'employee_name'])
            ->toJson();
    }

    public function create(Request $request, \App\Services\Payroll\SalaryService $salaryService)
    {
        if (!auth()->user()->hasPermissionTo('salary_process')) {
            abort(403, 'Unauthorized');
        }
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
            ->get();
            
        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->get();
        
        $previews = [];
        if ($request->year && $request->month) {
            $branchId = auth()->user()->activeConcernId();
            if ($request->employee_id) {
                $previews = [$salaryService->singlePreview($branchId, $request->employee_id, $request->year, $request->month)];
            } else {
                $previews = $salaryService->bulkPreview($branchId, $request->year, $request->month);
                // Remove already processed if it's bulk
                $previews = array_filter($previews, fn($p) => !$p['already_processed']);
            }
        }

        return view('payroll.salary_process.create', compact('paymentModes', 'employees', 'previews'));
    }

    /**
     * Store a newly created resource in storage.
     */
    protected function calculateSalary($totalDaysInMonth,$baseSalary, $attendanceSummary)
    {
        // Breakdown from the attendance summary
        $presentDays = $attendanceSummary['present'];
        $absentDays = $attendanceSummary['absent'];
        $lateDays = $attendanceSummary['late'];
        $earlyCheckoutDays = $attendanceSummary['early_checkout'];
        $lateAndEarlyCheckoutDays = $attendanceSummary['late_and_early_checkout'];
        $overtime = $attendanceSummary['overtime']; // in seconds

        // Step 1: Calculate Total Lates
       // $totalLates = $lateDays + $earlyCheckoutDays + ($lateAndEarlyCheckoutDays * 2);
        $totalLates = $lateDays;

        // Step 2: Determine how many lates are acceptable and how many result in a deduction
        $acceptableLates = 2; // First 2 lates are acceptable
        $remainingLates = max($totalLates - $acceptableLates, 0);

        // Step 3: Check if remaining lates result in a full day deduction
        $lateDeductionDays = 0;
        if ($remainingLates > 0 && $remainingLates <= 2) {
            // 1 or 2 lates result in 1 day deduction
            $lateDeductionDays = 1;
        } elseif ($remainingLates > 2) {
            // Every 3 lates = 1 day deduction
            $lateDeductionDays = floor($remainingLates / 3);
        }

        // Step 4: Calculate absent days deduction
        $absentDeductionDays = $absentDays;

        // Step 5: Calculate daily salary and deductions
        $totalWorkingDays = $totalDaysInMonth; // Assuming days in a month
        $dailySalary = $baseSalary / $totalWorkingDays;

        // Deduction for lates and absents
        $totalLateDeduction = $lateDeductionDays * $dailySalary;
        $totalAbsentDeduction = $absentDeductionDays * $dailySalary;
        $totalDeduction = $totalLateDeduction + $totalAbsentDeduction;
        // Step 6: Calculate Overtime Pay
        $overtimeInHours = $overtime / 3600; // Convert seconds to hours
        $hourlyRate = $dailySalary / 8; // Assuming 8 hours workday
        $overtimePay = $overtimeInHours * $hourlyRate;

        // Step 7: Calculate Final Salary
        $grossSalary = $baseSalary + $overtimePay;
        $finalSalary = $baseSalary - $totalDeduction + $overtimePay;

        // Return final salary breakdown
        return [
            'base_salary' => $baseSalary,
            'total_late_deductions' => round($totalLateDeduction),
            'total_absent_deductions' => round($totalAbsentDeduction),
            'total_deductions' => round($totalDeduction),
            'overtime_pay' => round($overtimePay),
            'gross_salary' => round($grossSalary),
            'final_salary' => round($finalSalary),
        ];
    }

    public function preview(Request $request, \App\Services\Payroll\SalaryService $salaryService)
    {
        if (!auth()->user()->hasPermissionTo('salary_process')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'year' => 'required',
            'month' => 'required',
        ]);

        $branchId = auth()->user()->activeConcernId();

        if ($request->employee_id) {
            $previews = [$salaryService->singlePreview($branchId, $request->employee_id, $request->year, $request->month)];
        } else {
            $previews = $salaryService->bulkPreview($branchId, $request->year, $request->month);
        }

        $html = view('payroll.salary_process.preview_table', compact('previews'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function store(\App\Http\Requests\Payroll\StoreSalaryProcessRequest $request)
    {

        DB::beginTransaction();
        try {
            $salaryService = new \App\Services\Payroll\SalaryService();
            $salaryService->processSalary($request->all(), auth()->id(), auth()->user()->activeConcernId());
            
            DB::commit();
            return redirect()->back()->with('success', 'Salary processed successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $exception->getMessage());

        }

    }



    /**
     * Show the payslip/details of the specified resource.
     */
    public function salaryItemView(SalaryProcessItem $salary_process_item)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_items')) {
            abort(403, 'Unauthorized');
        }
        if($salary_process_item->sister_concern_id != auth()->user()->activeConcernId()){
            abort(403, 'Unauthorized');
        }

        return view('payroll.salary_process.view', compact('salary_process_item'));
    }

    /**
     * Show the audit history modal for the specified resource.
     */
    public function salaryItemHistory(SalaryProcessItem $salary_process_item)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_items')) {
            abort(403, 'Unauthorized');
        }

        $logs = \App\Models\Microfinance\ActivityLog::with('user')
            ->where('loggable_type', SalaryProcessItem::class)
            ->where('loggable_id', $salary_process_item->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('payroll.salary_process.partial.history', compact('logs', 'salary_process_item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function salaryItemEdit(SalaryProcessItem $salary_process_item)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_item_edit')) {
            abort(403, 'Unauthorized');
        }

        if($salary_process_item->sister_concern_id != auth()->user()->activeConcernId()){
            abort(403, 'Unauthorized');
        }

        if ($salary_process_item->voucher_id) {
            return redirect()->route('salary-processes.details', $salary_process_item->salary_process_id)
                             ->with('error', 'This salary has already been disbursed and cannot be edited.');
        }
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
            ->get();
        return view('payroll.salary_process.edit',compact('salary_process_item',
        'paymentModes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function salaryItemUpdate(Request $request, SalaryProcessItem $salary_process_item, \App\Services\Payroll\SalaryService $salaryService)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_item_edit')) {
            abort(403, 'Unauthorized');
        }

        if ($salary_process_item->voucher_id) {
            return redirect()->back()->with('error', 'This salary has already been disbursed and cannot be edited.');
        }
        $request->validate([
            'basic_payable'=>'required|numeric|min:0',
            'house_rent'=>'nullable|numeric',
            'medical'=>'nullable|numeric',
            'overtime_allowance'=>'nullable|numeric',
            'fair_allowance'=>'nullable|numeric',
            'ta_da_allowance'=>'nullable|numeric',
            'gross'=>'nullable|numeric',
            'absent_deduction'=>'nullable|numeric',
            'late_deduction'=>'nullable|numeric',
            'penalty'=>'nullable|numeric',
            'pf_deduction'=>'nullable|numeric',
            'income_tax_deduction'=>'nullable|numeric',
            'advance'=>'nullable|numeric',
            'other_deduction'=>'nullable|numeric',
            'total_deduction'=>'nullable|numeric',
            'net_pay'=>'nullable|numeric',
        ]);
        try {
            DB::beginTransaction();
            $salaryProcess = $salary_process_item->salaryProcess;
            $paymentAccountHead = AccountHead::find($salaryProcess->payment_mode_account_head_id);
            $paymentMode = $paymentAccountHead ? $paymentAccountHead->id : null;
            $paymentModeType = $paymentAccountHead ? $paymentAccountHead->payment_mode : null;
            $employee = Employee::find($salary_process_item->employee_id);
            $request['year'] = $salary_process_item->year;
            $request['month'] = $salary_process_item->month;
            $request['process_date'] = $salaryProcess->process_date;
            $request['cheque_no'] = $salaryProcess->cheque_no;
            $request['cheque_date'] = $salaryProcess->cheque_date;

            $salaryItem = $salary_process_item;
            $salaryItem->basic_payable = $request->basic_payable ?? 0;
            $salaryItem->house_rent = $request->house_rent ?? 0;
            $salaryItem->medical = $request->medical ?? 0;
            $salaryItem->conveyance = 0;
            $salaryItem->overtime_allowance = $request->overtime_allowance ?? 0;
            $salaryItem->fair_allowance = $request->fair_allowance ?? 0;
            $salaryItem->ta_da_allowance = $request->ta_da_allowance ?? 0;
            $salaryItem->base_gross = $employee->gross_salary ?? 0;
            $salaryItem->gross = $request->gross ?? 0;

            $salaryItem->absent_deduction = $request->absent_deduction ?? 0;
            $salaryItem->late_deduction = $request->late_deduction ?? 0;
            $salaryItem->other_deduction = $request->other_deduction ?? 0;
            $salaryItem->penalty = $request->penalty ?? 0;
            $salaryItem->pf_deduction = $request->pf_deduction ?? 0;
            $salaryItem->tax_deduction = $request->income_tax_deduction ?? 0; // Keeping both in sync
            $salaryItem->income_tax_deduction = $request->income_tax_deduction ?? 0;
            $salaryItem->advance = $request->advance ?? 0;
            $salaryItem->total_deduction = $request->total_deduction ?? 0;
            $salaryItem->net_pay = $request->net_pay;
            $salaryItem->save();

            $employeeAccountHead = AccountHead::where('employee_id', $employee->id)->first();
            if ($employeeAccountHead) {
                $salaryService->generateSalaryVouchers(
                    $salaryItem,
                    $employeeAccountHead,
                    $paymentMode,
                    $paymentModeType,
                    auth()->id(),
                    auth()->user()->activeConcernId(),
                    $salaryProcess->process_date,
                    $salaryProcess->cheque_no
                );
            }

            DB::commit();
            return redirect()->back()->with('message','Salary updated');
        }catch (\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryProcess $salary_process)
    {
        DB::beginTransaction();
        try {
            if (!auth()->user()->hasPermissionTo('salary_process_delete')) {
                abort(403, 'Unauthorized');
            }
            foreach ($salary_process->salaryProcessItems as $salaryProcessItem){
                if ($salaryProcessItem->voucher) {
                    Voucher::where('id', $salaryProcessItem->voucher_id)->delete();
                    Transaction::where('voucher_id', $salaryProcessItem->voucher_id)->delete();
                }
                if ($salaryProcessItem->jVoucher) {
                    Voucher::where('id', $salaryProcessItem->jv_voucher_id)->delete();
                    Transaction::where('voucher_id', $salaryProcessItem->jv_voucher_id)->delete();
                }
                $salaryProcessItem->delete();
            }
            // Delete the Voucher record
            $salary_process->delete();
            // Return a JSON success response
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Salary deleted successfully'], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }
    }
    public function detailsItemDestroy(SalaryProcessItem $salary_process_item)
    {
        DB::beginTransaction();
        try {
            if (!auth()->user()->hasPermissionTo('salary_process_item_delete')) {
                abort(403, 'Unauthorized');
            }
                if ($salary_process_item->voucher) {
                    Voucher::where('id', $salary_process_item->voucher_id)->delete();
                    Transaction::where('voucher_id', $salary_process_item->voucher_id)->delete();
                }
                if ($salary_process_item->jVoucher) {
                    Voucher::where('id', $salary_process_item->jv_voucher_id)->delete();
                    Transaction::where('voucher_id', $salary_process_item->jv_voucher_id)->delete();
                }

            // Delete the Voucher record
            $salary_process_item->delete();
            // Return a JSON success response
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Salary process item deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }
    }

    public function disburseSelected(Request $request, \App\Services\Payroll\SalaryService $salaryService)
    {
        if (!auth()->user()->hasPermissionTo('salary_process_item_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'item_ids' => 'required|array',
            'payment_mode' => 'required',
            'disburse_date' => 'required|date',
        ]);

        $paymentModeType = 0;
        $paymentModeId = 0;
        if ($request->payment_mode != '') {
            $parts = explode('|', $request->payment_mode);
            if (count($parts) == 2) {
                $paymentModeId = $parts[0];
                $paymentModeType = $parts[1];
            }
        }

        if (!$paymentModeId || !$paymentModeType) {
            return response()->json(['success' => false, 'message' => 'Invalid Payment Mode'], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $items = SalaryProcessItem::whereIn('id', $request->item_ids)->get();
            $accountHeads = AccountHead::whereIn('employee_id', $items->pluck('employee_id')->filter()->unique())->get()->keyBy('employee_id');

            foreach ($items as $salaryItem) {
                if ($salaryItem->voucher_id) {
                    continue; // Already disbursed
                }

                $employeeAccountHead = $accountHeads->get($salaryItem->employee_id);
                if ($employeeAccountHead) {
                    $salaryService->generateSalaryVouchers(
                        $salaryItem,
                        $employeeAccountHead,
                        $paymentModeId,
                        $paymentModeType,
                        auth()->id(),
                        auth()->user()->activeConcernId(),
                        $request->disburse_date,
                        $request->cheque_no
                    );
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Salary disbursed successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
