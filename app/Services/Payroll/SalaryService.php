<?php

namespace App\Services\Payroll;

use App\Models\Hrm\Employee;
use App\Models\Hrm\Holiday;
use App\Models\Hrm\Leave;
use App\Models\Hrm\EmployeeMovement;
use App\Models\Payroll\SalaryProcessItem;
use App\Models\Payroll\SalaryProcess;
use App\Models\Account\AccountHead;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryService
{
    // =========================================================================
    // Preview — একজন বা সবার Salary calculate করো (DB write নেই)
    // =========================================================================

    /**
     * একটি নির্দিষ্ট মাসের জন্য সব Active Employee-এর salary preview তৈরি করো।
     * যাদের salary ইতিমধ্যে process হয়েছে তাদের skip করবে।
     */
    public function bulkPreview(int $branchId, int $year, int $month): array
    {
        [$startDate, $endDate] = $this->getMonthRange($year, $month);
        $totalDays = $this->monthDayCount($year, $month);

        $employees = Employee::with(['attendances' => function ($q) use ($startDate, $endDate) {
            $q->whereDate('punch_time', '>=', $startDate)
              ->whereDate('punch_time', '<=', $endDate)
              ->orderBy('punch_time', 'asc');
        }, 'department', 'designation'])
        ->where('sister_concern_id', $branchId)
        ->whereHas('employeeStatus', fn($q) => $q->where('is_salary_apply', 1))
        ->get();

        $previews = [];
        foreach ($employees as $employee) {
            $alreadyProcessed = SalaryProcessItem::where('employee_id', $employee->id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            $previews[] = [
                'employee'          => $employee,
                'already_processed' => $alreadyProcessed,
                'salary'            => $alreadyProcessed ? null : $this->calculateForEmployee($employee, $totalDays),
            ];
        }

        return $previews;
    }

    /**
     * একজন Employee-এর salary preview তৈরি করো।
     */
    public function singlePreview(int $branchId, int $employeeId, int $year, int $month): array
    {
        [$startDate, $endDate] = $this->getMonthRange($year, $month);
        $totalDays = $this->monthDayCount($year, $month);

        $employee = Employee::with(['attendances' => function ($q) use ($startDate, $endDate) {
            $q->whereDate('punch_time', '>=', $startDate)
              ->whereDate('punch_time', '<=', $endDate)
              ->orderBy('punch_time', 'asc');
        }, 'department', 'designation'])
        ->where('sister_concern_id', $branchId)
        ->findOrFail($employeeId);

        $alreadyProcessed = SalaryProcessItem::where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        return [
            'employee'          => $employee,
            'already_processed' => $alreadyProcessed,
            'salary'            => $alreadyProcessed ? null : $this->calculateForEmployee($employee, $totalDays),
        ];
    }

    // =========================================================================
    // Core Calculation
    // =========================================================================

    /**
     * একজন Employee-এর attendance summary + salary breakdown হিসাব করো।
     */
    public function calculateForEmployee(Employee $employee, int $totalDaysInMonth): array
    {
        $summary = $this->attendanceSummary($employee);
        $salary  = $this->calculateSalary($employee, $totalDaysInMonth, $summary);

        return array_merge($summary, $salary);
    }

    /**
     * Employee attendance summary (present, absent, late, overtime, etc.)
     */
    public function attendanceSummary(Employee $employee): array
    {
        $summary = [
            'present'               => 0,
            'absent'                => 0,
            'late'                  => 0,
            'no_checkout'           => 0,
            'early_checkout'        => 0,
            'late_and_early_checkout' => 0,
            'movement'              => 0,
            'public_holiday'        => 0,
            'weekend_holiday'       => 0,
            'leave'                 => 0,
            'overtime'              => 0, // seconds
        ];

        $daysAttendance = $employee->attendances->groupBy(function ($a) {
            return Carbon::parse($a->punch_time)->format('Y-m-d');
        });

        $firstDate = $employee->attendances->min(fn($a) => Carbon::parse($a->punch_time)->startOfMonth());
        $startDate = $employee->attendances->isNotEmpty()
            ? Carbon::parse($employee->attendances->first()->punch_time)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');

            if ($daysAttendance->has($dateString)) {
                $times      = $daysAttendance->get($dateString);
                $firstPunch = $times->first()->punch_time;
                $lastPunch  = $times->last()->punch_time;
                $status     = $this->determinePresentStatus($firstPunch, $lastPunch, $dateString);
                $overtime   = $this->overtimeCalc($firstPunch, $lastPunch);
                $summary['overtime'] += $overtime;
            } else {
                $status = $this->determineAbsentStatus($employee->id, $dateString);
            }

            if (isset($summary[$status['status']])) {
                $summary[$status['status']]++;
            }
        }

        return $summary;
    }

    /**
     * Salary breakdown (basic, deductions, overtime, net pay)
     */
    public function calculateSalary(Employee $employee, int $totalDaysInMonth, array $summary): array
    {
        $lateDays       = $summary['late'];
        $absentDays     = $summary['absent'];
        $overtimeSeconds = $summary['overtime'];

        $grossSalary    = $employee->gross_salary;
        $structure      = $employee->salaryStructure;

        // Calculate Salary Components based on Structure
        if ($structure) {
            $tadaAllowance = $structure->ta_da_fixed;
            $baseForPercent = max($grossSalary - $tadaAllowance, 0);

            $basicSalary = ($baseForPercent * $structure->basic_percent) / 100;
            $houseRent   = ($baseForPercent * $structure->house_rent_percent) / 100;
            $medical     = ($baseForPercent * $structure->medical_percent) / 100;
            $pfDeduction = ($baseForPercent * $structure->pf_percent) / 100;
            $taxDeduction= ($baseForPercent * $structure->tax_percent) / 100;
        } else {
            // Fallback default structure
            $tadaAllowance = 0;
            $baseForPercent = $grossSalary;

            $basicSalary = ($baseForPercent * 60) / 100;
            $houseRent   = ($baseForPercent * 30) / 100;
            $medical     = ($baseForPercent * 10) / 100;
            $pfDeduction = 0;
            $taxDeduction= 0;
        }

        // Lates & Absents Deduction (Using Gross/TotalDays as daily rate)
        $acceptableLates  = 2;
        $remainingLates   = max($lateDays - $acceptableLates, 0);
        $lateDeductionDays = 0;

        if ($remainingLates > 0 && $remainingLates <= 2) {
            $lateDeductionDays = 1;
        } elseif ($remainingLates > 2) {
            $lateDeductionDays = floor($remainingLates / 3);
        }

        $dailySalary         = $grossSalary / $totalDaysInMonth;
        $totalLateDeduction  = round($lateDeductionDays * $dailySalary);
        $totalAbsentDeduction = round($absentDays * $dailySalary);
        
        // Employee Advance Balance (Due Deduction)
        $advanceBalance = round($employee->advanceBalance());
        $advanceDeduction = $advanceBalance > 0 ? $advanceBalance : 0;

        $totalDeduction      = $totalLateDeduction + $totalAbsentDeduction + round($pfDeduction) + round($taxDeduction) + $advanceDeduction;

        // Overtime
        $overtimeInHours = $overtimeSeconds / 3600;
        $hourlyRate      = $dailySalary / 8;
        $overtimePay     = round($overtimeInHours * $hourlyRate);

        $calculatedGross  = round($grossSalary + $overtimePay);
        $finalSalary  = round($grossSalary - $totalDeduction + $overtimePay);

        // Optional: Ensure Final Salary doesn't go negative due to huge advance.
        if ($finalSalary < 0) {
            // Deduct only what's possible and keep final at 0
            $advanceDeduction = $advanceDeduction + $finalSalary; // Final is negative, so this reduces advance
            $totalDeduction = $totalLateDeduction + $totalAbsentDeduction + round($pfDeduction) + round($taxDeduction) + $advanceDeduction;
            $finalSalary = 0;
        }

        return [
            'base_salary'             => round($basicSalary),
            'house_rent'              => round($houseRent),
            'medical'                 => round($medical),
            'ta_da_allowance'         => round($tadaAllowance),
            'pf_deduction'            => round($pfDeduction),
            'tax_deduction'           => round($taxDeduction),
            'daily_salary'            => round($dailySalary, 2),
            'total_days_in_month'     => $totalDaysInMonth,
            'late_deduction_days'     => $lateDeductionDays,
            'total_late_deductions'   => $totalLateDeduction,
            'total_absent_deductions' => $totalAbsentDeduction,
            'advance'                 => $advanceDeduction,
            'total_deductions'        => $totalDeduction,
            'overtime_seconds'        => $overtimeSeconds,
            'overtime_pay'            => $overtimePay,
            'gross_salary'            => $calculatedGross,
            'final_salary'            => $finalSalary,
            'net_pay'                 => $finalSalary,
        ];
    }

    // =========================================================================
    // Helper: Date Range
    // =========================================================================

    public function getMonthRange(int $year, int $month): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay()->format('Y-m-d');
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay()->format('Y-m-d');
        return [$start, $end];
    }

    public function monthDayCount(int $year, int $month): int
    {
        return Carbon::createFromDate($year, $month, 1)->daysInMonth;
    }

    // =========================================================================
    // Attendance Status Helpers (ReportController থেকে নেওয়া, Service-এ centralized)
    // =========================================================================

    private function determinePresentStatus($firstPunch, $lastPunch, $date): array
    {
        $first = Carbon::parse($firstPunch);
        $last  = Carbon::parse($lastPunch);

        if ($first->equalTo($last)) {
            return ['status' => 'no_checkout'];
        }

        $dateCarbon    = Carbon::parse($date);
        $lateLimit     = Carbon::parse($date . ' 09:10:00');
        $checkoutLimit = Carbon::parse($date . ' 17:00:00');

        $isLate          = $first->greaterThan($lateLimit);
        $isEarlyCheckout = $last->lessThan($checkoutLimit);

        if ($isLate && $isEarlyCheckout) return ['status' => 'late_and_early_checkout'];
        if ($isLate)                      return ['status' => 'late'];
        if ($isEarlyCheckout)             return ['status' => 'early_checkout'];

        return ['status' => 'present'];
    }

    private function determineAbsentStatus(int $employeeId, string $date): array
    {
        $carbonDate = Carbon::parse($date);

        if ($carbonDate->isFriday()) return ['status' => 'weekend_holiday'];

        $holiday = Holiday::where(fn($q) =>
            $q->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)
        )->first();
        if ($holiday) return ['status' => 'public_holiday'];

        $leave = Leave::where('employee_id', $employeeId)
            ->where(fn($q) => $q->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date))
            ->where('status', 'approved')
            ->first();
        if ($leave) return ['status' => 'leave'];

        $movement = EmployeeMovement::where('employee_id', $employeeId)
            ->where(fn($q) => $q->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date))
            ->where('status', 'approved')
            ->first();
        if ($movement) return ['status' => 'movement'];

        return ['status' => 'absent'];
    }

    private function overtimeCalc($firstPunch, $lastPunch): int
    {
        $diff = Carbon::parse($firstPunch)->diffInSeconds(Carbon::parse($lastPunch));
        $eightHours = 8 * 3600;
        return $diff > $eightHours ? $diff - $eightHours : 0;
    }
    // =========================================================================
    // Save Salary Process
    // =========================================================================

    public function processSalary(array $requestData, int $userId, int $branchId): void
    {
        DB::beginTransaction();
        try {
            $year = $requestData['year'];
            $month = $requestData['month'];
            
            if (empty($requestData['employees'])) {
                throw new \Exception('No employee data submitted.');
            }
            
            $employeeIds = array_keys($requestData['employees']);
            $alreadyProcessedEmployees = SalaryProcessItem::whereIn('employee_id', $employeeIds)
                ->where('year', $year)
                ->where('month', $month)
                ->pluck('employee_id')
                ->toArray();

            if (!empty($alreadyProcessedEmployees)) {
                $firstProcessed = Employee::find($alreadyProcessedEmployees[0]);
                if ($firstProcessed) {
                    throw new \Exception('Salary for ' . $firstProcessed->name . ' (' . $firstProcessed->member_no . ') is already processed for this month.');
                }
                throw new \Exception('One or more selected employees have already been processed for this month.');
            }
            
            $salary = SalaryProcess::where('sister_concern_id', $branchId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $activeEmployees = Employee::where('sister_concern_id', $branchId)
                ->whereHas('employeeStatus', fn($q) => $q->where('is_salary_apply', 1))
                ->count();
                
            if ($activeEmployees == 0) {
                throw new \Exception('No active employees');
            }

            $paymentMode = 0;
            $paymentModeType = 0;
            if (isset($requestData['payment_mode']) && $requestData['payment_mode'] != '') {
                [$paymentMode, $paymentModeType] = explode('|', $requestData['payment_mode']);
                
                if ($paymentMode > 0) {
                    $paymentAccount = AccountHead::find($paymentMode);
                    if ($paymentAccount) {
                        $totalNetPay = 0;
                        if (!empty($requestData['employees'])) {
                            foreach ($requestData['employees'] as $data) {
                                $gross = ($data['basic_payable'] ?? 0) + ($data['house_rent'] ?? 0) + ($data['medical'] ?? 0) + ($data['overtime_allowance'] ?? 0) + ($data['fair_allowance'] ?? 0) + ($data['ta_da_allowance'] ?? 0);
                                $totalDeduction = ($data['absent_deduction'] ?? 0) + ($data['late_deduction'] ?? 0) + ($data['pf_deduction'] ?? 0) + ($data['tax_deduction'] ?? 0) + ($data['penalty'] ?? 0) + ($data['advance'] ?? 0) + ($data['other_deduction'] ?? 0);
                                $netPay = $data['net_pay'] ?? ($gross - $totalDeduction);
                                $totalNetPay += $netPay;
                            }
                        }
                        
                        if (!$paymentAccount->isSufficient($totalNetPay, $branchId)) {
                            throw new \Exception('Insufficient balance in the selected payment account. Required amount: ৳' . number_format($totalNetPay, 2));
                        }
                    }
                }
            }

            if (!$salary) {
                $salary = new SalaryProcess();
                $salary->sister_concern_id = $branchId;
                $salary->year = $year;
                $salary->month = $month;
                $salary->payment_mode_account_head_id = $paymentMode;
                $salary->cheque_no = $paymentMode == 1 ? $requestData['cheque_no'] : null;
                $salary->cheque_date = $paymentMode == 1 ? Carbon::parse($requestData['cheque_date'])->format('Y-m-d') : null;
                $salary->process_date = Carbon::parse($requestData['process_date'])->format('Y-m-d');
                $salary->create_user_id = $userId;
                $salary->save();
            }



            $employeeIds = array_keys($requestData['employees']);
            $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
            $accountHeads = AccountHead::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

            $totalDaysInMonth = $this->monthDayCount($year, $month);

            foreach ($requestData['employees'] as $empId => $data) {
                $employee = $employees->get($empId);
                if (!$employee) continue;

                $present = $data['present'] ?? 0;
                $absent = $data['absent'] ?? 0;
                $late = $data['late'] ?? 0;
                $noCheckout = $data['no_checkout'] ?? 0;
                $earlyCheckout = $data['early_checkout'] ?? 0;
                $lateEarlyCheckout = $data['late_early_checkout'] ?? 0;
                $movement = $data['movement'] ?? 0;
                $publicHoliday = $data['public_holiday'] ?? 0;
                $weekendHoliday = $data['weekend_holiday'] ?? 0;
                $leave = $data['leave'] ?? 0;
                $overtime = $data['overtime_second'] ?? 0;

                $calculated = $this->calculateSalary($employee, $totalDaysInMonth, [
                    'late' => $late,
                    'absent' => $absent,
                    'overtime' => $overtime
                ]);

                $basicPayable = $calculated['base_salary'];
                $houseRent = $calculated['house_rent'];
                $medical = $calculated['medical'];
                $tadaAllowance = $calculated['ta_da_allowance'];
                $pfDeduction = $calculated['pf_deduction'];
                $taxDeduction = $calculated['tax_deduction'];
                
                $overtimeAllowance = $data['overtime_allowance'] ?? 0;
                $fairAllowance = $data['fair_allowance'] ?? 0;

                $gross = $basicPayable + $houseRent + $medical + $tadaAllowance + $overtimeAllowance + $fairAllowance;

                $absentDeduction = $data['absent_deduction'] ?? 0;
                $lateDeduction = $data['late_deduction'] ?? 0;
                $penalty = $data['penalty'] ?? 0;
                $advance = $data['advance'] ?? 0;
                $otherDeductionRaw = $data['other_deduction'] ?? 0;

                $totalDeduction = $absentDeduction + $lateDeduction + $pfDeduction + $taxDeduction + $penalty + $advance + $otherDeductionRaw;
                $netPay = $data['net_pay'] ?? ($gross - $totalDeduction);

                $salaryItem = new SalaryProcessItem();
                $salaryItem->sister_concern_id = $branchId;
                $salaryItem->bank_account_no = $employee->bank_account_no;
                $salaryItem->salary_process_id = $salary->id;
                $salaryItem->year = $year;
                $salaryItem->month = $month;
                $salaryItem->employee_id = $employee->id;
                
                $salaryItem->present = $present;
                $salaryItem->absent = $absent;
                $salaryItem->late = $late;
                $salaryItem->no_checkout = $noCheckout;
                $salaryItem->early_checkout = $earlyCheckout;
                $salaryItem->late_early_checkout = $lateEarlyCheckout;
                $salaryItem->movement = $movement;
                $salaryItem->public_holiday = $publicHoliday;
                $salaryItem->weekend_holiday = $weekendHoliday;
                $salaryItem->leave = $leave;
                $salaryItem->overtime_second = $overtime;

                $salaryItem->basic_payable = $basicPayable;
                $salaryItem->house_rent = $houseRent;
                $salaryItem->medical = $medical;
                $salaryItem->conveyance = 0;
                
                $salaryItem->overtime_allowance = $overtimeAllowance;
                $salaryItem->fair_allowance = $fairAllowance;
                $salaryItem->ta_da_allowance = $tadaAllowance;
                $salaryItem->base_gross = $employee->gross_salary ?? 0;
                $salaryItem->gross = $gross;
                
                $salaryItem->absent_deduction = $absentDeduction;
                $salaryItem->late_deduction = $lateDeduction;
                $salaryItem->penalty = $penalty;
                $salaryItem->advance = $advance;
                $salaryItem->pf_deduction = $pfDeduction;
                $salaryItem->tax_deduction = $taxDeduction;
                $salaryItem->other_deduction = $otherDeductionRaw;
                $salaryItem->total_deduction = $totalDeduction;
                $salaryItem->net_pay = $netPay;
                $salaryItem->save();

                $employeeAccountHead = $accountHeads->get($employee->id);
                if ($employeeAccountHead) {
                    $this->generateSalaryVouchers(
                        $salaryItem, 
                        $employeeAccountHead, 
                        $paymentMode, 
                        $paymentModeType, 
                        $userId, 
                        $branchId, 
                        $requestData['process_date'], 
                        $requestData['cheque_no'] ?? null
                    );
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function generateSalaryVouchers(SalaryProcessItem $salaryItem, $employeeAccountHead, $paymentMode, $paymentModeType, $userId, $branchId, $processDate, $chequeNo = null)
    {
        $year = $salaryItem->year;
        $month = $salaryItem->month;
        $gross = $salaryItem->gross;

        // Create or Update Journal Voucher: Salary Expense
        $notes = 'Salary Expense ' . $year . ' - ' . numberToMonthName($month);
        
        $jVoucher = null;
        if ($salaryItem->jv_voucher_id) {
            $jVoucher = Voucher::find($salaryItem->jv_voucher_id);
            if ($jVoucher) {
                Transaction::where('voucher_id', $jVoucher->id)->delete();
            }
        }
        
        if (!$jVoucher) {
            $jVoucher = new Voucher();
            $jVoucher->sister_concern_id = $branchId;
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $jVoucher->voucher_no_group_sl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            $jVoucher->voucher_no = $voucherNo;
        }

        $jVoucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $jVoucher->amount = $gross;
        $jVoucher->date = Carbon::parse($processDate)->format('Y-m-d');
        $jVoucher->account_head_payee_depositor_id = $employeeAccountHead->id;
        $jVoucher->notes = $notes;
        $jVoucher->user_id = $userId;
        $jVoucher->save();

        // JV Debit: Salary Expense (Gross)
        $transaction = new Transaction();
        $transaction->sister_concern_id = $branchId;
        $transaction->voucher_id = $jVoucher->id;
        $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::SALARY_EXPENSE);
        $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $transaction->transaction_type = TransactionType::$DEBIT;
        $transaction->amount = $gross;
        $transaction->date = $jVoucher->date;
        $transaction->voucher_no_group_sl = $jVoucher->voucher_no_group_sl;
        $transaction->voucher_no = $jVoucher->voucher_no;
        $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
        $transaction->notes = $notes;
        $transaction->user_id = $userId;
        $transaction->save();

        // JV Credit: Employee Payable (Net Pay)
        $transaction = new Transaction();
        $transaction->sister_concern_id = $branchId;
        $transaction->voucher_id = $jVoucher->id;
        $transaction->account_head_id = $employeeAccountHead->id;
        $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $transaction->transaction_type = TransactionType::$CREDIT;
        $transaction->amount = $salaryItem->net_pay;
        $transaction->date = $jVoucher->date;
        $transaction->voucher_no_group_sl = $jVoucher->voucher_no_group_sl;
        $transaction->voucher_no = $jVoucher->voucher_no;
        $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
        $transaction->notes = $notes;
        $transaction->user_id = $userId;
        $transaction->save();

        // JV Credit: Advance Deduction (Credits the Employee Advance Asset Account and reduces Employee balance)
        if ($salaryItem->advance > 0) {
            $transaction = new Transaction();
            $transaction->sister_concern_id = $branchId;
            $transaction->voucher_id = $jVoucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::EMPLOYEE_ADVANCE);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $salaryItem->advance;
            $transaction->date = $jVoucher->date;
            $transaction->voucher_no_group_sl = $jVoucher->voucher_no_group_sl;
            $transaction->voucher_no = $jVoucher->voucher_no;
            $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
            $transaction->employee_id = $salaryItem->employee_id; // CRITICAL: Tag employee to reduce their due advance!
            $transaction->notes = 'Advance recovery from salary: ' . $notes;
            $transaction->user_id = $userId;
            $transaction->save();
        }

        // JV Credit: Tax Deduction
        $taxAmount = $salaryItem->tax_deduction > 0 ? $salaryItem->tax_deduction : $salaryItem->income_tax_deduction;
        if ($taxAmount > 0) {
            $transaction = new Transaction();
            $transaction->sister_concern_id = $branchId;
            $transaction->voucher_id = $jVoucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::INCOME_TAX_DEDUCTION);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $taxAmount;
            $transaction->date = $jVoucher->date;
            $transaction->voucher_no_group_sl = $jVoucher->voucher_no_group_sl;
            $transaction->voucher_no = $jVoucher->voucher_no;
            $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
            $transaction->notes = 'Tax deduction: ' . $notes;
            $transaction->user_id = $userId;
            $transaction->save();
        }

        // JV Credit: Other Deductions (Absent, Late, Penalty, PF, Others)
        $otherDeductionAmount = $salaryItem->total_deduction - $salaryItem->advance - $taxAmount;
        if ($otherDeductionAmount > 0) {
            $transaction = new Transaction();
            $transaction->sister_concern_id = $branchId;
            $transaction->voucher_id = $jVoucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::ABSENT_LATE_OTHER_DEDUCTION);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $otherDeductionAmount;
            $transaction->date = $jVoucher->date;
            $transaction->voucher_no_group_sl = $jVoucher->voucher_no_group_sl;
            $transaction->voucher_no = $jVoucher->voucher_no;
            $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
            $transaction->notes = 'Other deductions (Absent/Late/PF): ' . $notes;
            $transaction->user_id = $userId;
            $transaction->save();
        }

        $salaryItem->jv_voucher_id = $jVoucher->id;
        $salaryItem->save();

        // Create or Update Payment Voucher (Only if payment mode is provided)
        if ($paymentMode && $paymentModeType) {
            $notes = 'Salary Payment ' . $year . ' - ' . numberToMonthName($month);
            
            $pVoucher = null;
            if ($salaryItem->voucher_id) {
            $pVoucher = Voucher::find($salaryItem->voucher_id);
            if ($pVoucher) {
                Transaction::where('voucher_id', $pVoucher->id)->delete();
            }
        }
        
        if (!$pVoucher) {
            $pVoucher = new Voucher();
            $pVoucher->sister_concern_id = $branchId;
            $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
            $pVoucher->voucher_no_group_sl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            $pVoucher->voucher_no = $voucherNo;
        }

        $pVoucher->payment_type_id = $paymentModeType;
        $pVoucher->payment_account_head_id = $paymentMode;
        $pVoucher->voucher_type = VoucherType::$PAYMENT_VOUCHER;
        $pVoucher->amount = $salaryItem->net_pay;
        $pVoucher->date = Carbon::parse($processDate)->format('Y-m-d');
        $pVoucher->account_head_payee_depositor_id = $employeeAccountHead->id;
        $pVoucher->cheque_no = $chequeNo;
        $pVoucher->notes = $notes;
        $pVoucher->user_id = $userId;
        $pVoucher->save();

        // PV Credit: Bank/Cash
        $transaction = new Transaction();
        $transaction->sister_concern_id = $branchId;
        $transaction->voucher_id = $pVoucher->id;
        $transaction->payment_type_id = $paymentModeType;
        $transaction->payment_account_head_id = $paymentMode;
        $transaction->account_head_id = $paymentMode;
        $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
        $transaction->transaction_type = TransactionType::$CREDIT;
        $transaction->amount = $salaryItem->net_pay;
        $transaction->date = $pVoucher->date;
        $transaction->voucher_no_group_sl = $pVoucher->voucher_no_group_sl;
        $transaction->voucher_no = $pVoucher->voucher_no;
        $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
        $transaction->cheque_no = $chequeNo;
        $transaction->notes = $notes;
        $transaction->user_id = $userId;
        $transaction->save();

        // PV Debit: Employee Payable
        $transaction = new Transaction();
        $transaction->sister_concern_id = $branchId;
        $transaction->voucher_id = $pVoucher->id;
        $transaction->payment_type_id = $paymentModeType;
        $transaction->payment_account_head_id = $paymentMode;
        $transaction->account_head_id = $employeeAccountHead->id;
        $transaction->voucher_type = VoucherType::$PAYMENT_VOUCHER;
        $transaction->transaction_type = TransactionType::$DEBIT;
        $transaction->amount = $salaryItem->net_pay;
        $transaction->date = $pVoucher->date;
        $transaction->voucher_no_group_sl = $pVoucher->voucher_no_group_sl;
        $transaction->voucher_no = $pVoucher->voucher_no;
        $transaction->account_head_payee_depositor_id = $employeeAccountHead->id;
        $transaction->cheque_no = $chequeNo;
        $transaction->notes = $notes;
        $transaction->user_id = $userId;
        $transaction->save();

        $salaryItem->voucher_id = $pVoucher->id;
        $salaryItem->save();
        }
    }

    public function generateSalaryVouchersBulk($salaryItems, $paymentMode, $paymentModeType, $userId, $branchId, $processDate, $chequeNo = null) {
        // We will create this method for bulk PV creation in the future if needed
    }
}
