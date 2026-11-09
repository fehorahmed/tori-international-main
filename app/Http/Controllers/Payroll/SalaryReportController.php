<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Department;
use App\Models\Hrm\Employee;
use App\Models\Payroll\SalaryProcessItem;
use Illuminate\Http\Request;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Models\Account\AccountHead;

class SalaryReportController extends Controller
{
    public function salarySheet(Request $request)
    {
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
        $salaries = [];
        $salaryMonth = null;
        $selectDepartment = null;
        $totalDaysInMonth = 0;
        $query = SalaryProcessItem::where('sister_concern_id',auth()->user()->activeConcernId());
        if ($request->employee != ''){
            $query->where('employee_id',$request->employee);
        }
        if ($request->year != '' && $request->month != ''){

            $query->where('year',$request->year)
                ->where('month',$request->month);

            $salaries = $query->with('employee')
                ->get()
                ->sortBy('employee.id_no');

            $totalDaysInMonth = monthDayCount($request->year, $request->month);
            $salaryMonth = numberToMonthName(request('month')).'-'.request('year');
        }


        if ($request->has('search')) {
            return view('payroll.report.salary_sheet_report', compact(
                'selectDepartment', 'salaries', 'totalDaysInMonth', 'salaryMonth',
                'employees'
            ));
        }

        return view('payroll.report.salary_sheet',compact(
            'selectDepartment','salaries','totalDaysInMonth','salaryMonth',
        'employees'));
    }

    public function employeeAdvanceReport(Request $request)
    {
        $employees = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->get();

        $query = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->withAdvanceBalance();

        if ($request->employee_id) {
            $query->where('id', $request->employee_id);
        }

        $reportData = $query->get()->map(function($employee) {
            $total_amount = $employee->total_debit ?? 0;
            $total_paid = $employee->total_credit ?? 0;
            
            return (object) [
                'employee' => $employee,
                'total_amount' => $total_amount,
                'total_paid' => $total_paid,
                'balance' => $total_amount - $total_paid
            ];
        });

        if ($request->has('search')) {
            return view('payroll.report.employee_advance_report', compact('employees', 'reportData'));
        }

        return view('payroll.report.employee_advance', compact('employees', 'reportData'));
    }
}
