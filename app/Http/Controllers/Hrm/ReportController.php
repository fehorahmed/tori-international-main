<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\EmployeeStatus;
use Illuminate\Http\Request;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Holiday;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Attendance;
use App\Models\Hrm\EmployeeMovement;
use App\Models\Hrm\Leave;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Hrm\AttendanceReminderLog;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function departmentReport(Request $request)
    {
        $query = Department::query();
        $departments = $query->orderBy('name')->get();
        return view('hrm.reports.department_report', compact('departments'));
    }

    public function designationReport(Request $request)
    {
        $query = Designation::query();
        $designations = $query->orderBy('name')->get();
        return view('hrm.reports.designation_report', compact('designations'));
    }

    public function holidayReport(Request $request)
    {
        $query = Holiday::query();
        
        $holidays = $query->orderBy('start_date', 'desc')->get();
        return view('hrm.reports.holiday_report', compact('holidays'));
    }

    public function employeeReport(Request $request)
    {
        $query = Employee::where('sister_concern_id', auth()->user()->activeConcernId());
        
        if ($request->employee_status_id != '') {
            $query->where('employee_status_id', $request->employee_status_id);
        }
        
        $employees = $query->with(['department', 'designation', 'sisterConcern', 'employeeStatus'])->orderBy('name')->get();
        $employeeStatuses = EmployeeStatus::all();
        return view('hrm.reports.employee_report', compact('employees', 'employeeStatuses'));
    }

    public function liveAttendanceReport(Request $request)
    {
        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->get();

        $employee_attendances = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $employeeId = $request->employee;
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');

            $attendanceQuery = Employee::query();
            $attendanceQuery->where('sister_concern_id', auth()->user()->activeConcernId());

            $attendanceQuery->with(['attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereDate('punch_time', '>=', $startDate)
                    ->whereDate('punch_time', '<=', $endDate)
                    ->orderBy('punch_time', 'asc');

            }]);

            if ($employeeId != '' && $employeeId != 0) {
                $attendanceQuery->where('id', $employeeId);
            }
            $employee_attendances = $attendanceQuery->get();
            
        }
        return view('hrm.reports.live_attendance', compact('employees', 'employee_attendances'));
    }

    protected function determinePresentStatus($firstPunch, $lastPunch, $date)
    {
        // Convert punch times to Carbon instances
        $firstPunch = Carbon::parse($firstPunch);
        $lastPunch = Carbon::parse($lastPunch);
        $date = Carbon::parse($date);

        // If firstPunch and lastPunch are the same, skip further checks
        // Placeholder for public holiday check
        $noCheckout = $this->isNoCheckout($firstPunch,$lastPunch);
        if ($noCheckout) {
            return $noCheckout;
        }

        // Late check: Compare first punch time with the attendance time limit
        $lateEarlyCheckOut = $this->isLateEarlyCheckOut($date,$firstPunch,$lastPunch);
        if ($lateEarlyCheckOut) {
            return $lateEarlyCheckOut;
        }

        // or Present
        return $this->isPresent();


    }
    protected function isNoCheckout($firstPunch,$lastPunch){
        if ($firstPunch->equalTo($lastPunch)) {
            return [
                'status'=>'no_checkout',
                'message'=>'<span class="text-danger">No Checkout</span>'
            ];
        }
        return false;
    }
    protected function isLateEarlyCheckOut($date,$firstPunch,$lastPunch){
        $attendTimeLimit = Carbon::parse($date->format('Y-m-d') . ' 09:10:00');

        $isLate = $firstPunch->greaterThan($attendTimeLimit);
        // Early Checkout check: Compare last punch time with the end of day time
        $endOfDayLimit = Carbon::parse($date->format('Y-m-d') . ' 17:00:00');
        $isEarlyCheckout = $lastPunch->lessThan($endOfDayLimit);

        // Determine status based on both conditions
        if ($isLate && $isEarlyCheckout) {
            return [
                'status'=>'late_and_early_checkout',
                'message'=>'<span class="text-warning">Late and Early Checkout</span>'
            ];
        } elseif ($isLate) {
            return [
                'status'=>'late',
                'message'=>'<span class="text-warning">Late</span>'
            ];
        } elseif ($isEarlyCheckout) {
            return [
                'status'=>'early_checkout',
                'message'=>'<span class="text-warning">Early Checkout</span>'
            ];
        }
        return false;
    }
    protected function isPresent()
    {
        return [
            'status'=>'present',
            'message'=>'<span class="text-success">Present</span>'
        ];
    }
    protected function checkCheckOut($firstPunch, $lastPunch)
    {
        // Convert punch times to Carbon instances
        $firstPunch = Carbon::parse($firstPunch);
        $lastPunch = Carbon::parse($lastPunch);

        // If firstPunch and lastPunch are the same, skip further checks
        if ($firstPunch->equalTo($lastPunch)) {
            return '';
        } else {
            return Carbon::parse($lastPunch)->format('h:i:s a');
        }

    }

    protected function determineAbsentStatus($employeeId, $date)
    {
        $date = Carbon::parse($date);

        // Consider Weekend (Friday) as Holiday
        $friday = $this->isFriday($date);
        if ($friday) {
            return $friday;
        }

        // Placeholder for public holiday check
        $holiday = $this->isPublicHoliday($date);
        if ($holiday) {
            return $holiday;
         }

        // Placeholder for leave check
        $leave = $this->isOnLeave($employeeId, $date);
        if ($leave) {
            return $leave;
        }
        // Check for Employee Movement
        $movement = $this->isOnMovement($employeeId, $date);
        if ($movement) {
            return $movement;
        }
        // or Absent
        return $this->isAbsent();
    }
    protected function isFriday($date)
    {
        $date = Carbon::parse($date); // Ensure $date is a Carbon instance
        if ($date->isFriday()){
            return [
                'status'=>'weekend_holiday',
                'message'=>'<span class="text-indigo">Weekend Holiday</span>'
            ];
        }
        return false; // No holiday record found
    }
    protected function isPublicHoliday($date)
    {
        $date = Carbon::parse($date);
        $holiday = Holiday::where(function ($query) use ($date) {
            $query->whereDate('start_date', '<=', $date->format('Y-m-d'))
                ->whereDate('end_date', '>=', $date->format('Y-m-d'));
        })->first();
        if ($holiday) {
            return [
                'status'=>'public_holiday',
                'message'=>'<span class="text-info">Public Holiday: '.$holiday->title.'</span>'
            ];
        }
        return false;
    }
    protected function isOnLeave($employeeId, $date)
    {
        $date = Carbon::parse($date);
        $leave = Leave::where('employee_id', $employeeId)
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date->format('Y-m-d'))
                    ->whereDate('end_date', '>=', $date->format('Y-m-d'));
            })
            ->where('status', 'approved')
            ->with('leaveType')
            ->first();
        if ($leave) {
            return [
                'status'=>'leave',
                'message'=>'<span class="text-info">' . $leave->leaveType->name . '</span>'
            ];
        }
        return false;
    }
    protected function isOnMovement($employeeId, $date)
    {
        $date = Carbon::parse($date);
        $movement = EmployeeMovement::where('employee_id', $employeeId)
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date->format('Y-m-d'))
                    ->whereDate('end_date', '>=', $date->format('Y-m-d'));
            })->where('status', 'approved')
            ->first();

        if ($movement) {
            $movementReason = 'Movement: '.$movement->from_location . ' to ' . $movement->to_location;
            if ($movement->reason != '') {
                $movementReason .= '(' . $movement->reason . ')';
            }
            return [
                'status'=>'movement',
                'message'=>'<span class="text-success">' . $movementReason . '</span>',
            ];
        }

        return false;
    }
    protected function isAbsent()
    {

        return [
            'status'=>'absent',
            'message'=>'<span class="text-danger">Absent</span>'
        ];
    }

    protected function overtimeCalc($firstPunch, $lastPunch)
    {
        // Convert punch times to Carbon instances if they are not already
        $firstPunch = Carbon::parse($firstPunch);
        $lastPunch = Carbon::parse($lastPunch);
        // Calculate total time difference in seconds
        $totalDurationInSeconds = $firstPunch->diffInSeconds($lastPunch);

        // 8 hours in seconds
        $eightHoursInSeconds = 8 * 3600;
        // Check if total duration exceeds 8 hours
        if ($totalDurationInSeconds > $eightHoursInSeconds) {
            // Calculate overtime in seconds
           return $totalDurationInSeconds - $eightHoursInSeconds;

        } else {
            return 0;
        }
    }
    protected function overtimeCalcHours($overtimeInSeconds)
    {
        $hours = floor($overtimeInSeconds / 3600);
        $minutes = floor(($overtimeInSeconds % 3600) / 60);
        $seconds = $overtimeInSeconds % 60;

        $result = '';

        // Only append hours if greater than 0
        if ($hours > 0) {
            $result .= "{$hours}h ";
        }

        // Only append minutes if greater than 0
        if ($minutes > 0) {
            $result .= "{$minutes}m ";
        }

        // Only append seconds if greater than 0
        if ($seconds > 0) {
            $result .= "{$seconds}s";
        }

        // Trim any extra spaces from the result
        return trim($result);
    }

    public function attendanceSheet(Request $request)
    {
        $report = [];
        $selectEmployee = null;
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
//        $septemberStart = Carbon::create(2024, 9, 1, 0, 0, 0);
//        $septemberEnd = Carbon::create(2024, 9, 30, 23, 59, 59);
//
//        foreach ($employees as $employee) {
//            // Loop through each day of September
//            for ($date = $septemberStart->copy(); $date->lte($septemberEnd); $date->addDay()) {
//                // Skip Fridays
//                if ($date->isFriday()) {
//                    continue;
//                }
//
//                // Define the punch times with both date and time for the day
//                $punchTimes = [
//                    $date->copy()->setTime(9, 0, 0),  // 9:00 AM
//                    $date->copy()->setTime(11, 0, 0), // 11:00 AM
//                    $date->copy()->setTime(14, 0, 0), // 2:00 PM
//                    $date->copy()->setTime(17, 0, 0), // 5:00 PM
//                ];
//
//                // Insert the punch times with date and time for the current day
//                foreach ($punchTimes as $punchTime) {
//                    $attendance = new Attendance();
//                    $attendance->employee_id = $employee->id;
//                    $attendance->punch_time = $punchTime; // Date and time
//                    $attendance->save();
//                }
//            }
//        }
//        dd('done');

        if ($request->start_date != '' && $request->end_date != '') {
            $employeeId = $request->employee;
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $attendanceQuery = Employee::with(['attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereDate('punch_time', '>=', $startDate)
                    ->whereDate('punch_time', '<=', $endDate)
                    ->orderBy('punch_time', 'asc');
            }]);

            if ($employeeId != '') {
                $selectEmployee = Employee::find($employeeId);
                $attendanceQuery->where('id', $employeeId);
            }

            $attendances = $attendanceQuery->get();

            $report = $attendances->map(function ($employee) use ($startDate, $endDate) {
                // Group the attendance records by date
                $daysAttendance = $employee->attendances->groupBy(function ($attendance) {
                    return Carbon::parse($attendance->punch_time)->format('Y-m-d');
                });

                $reportData = collect();

                // Loop through each date in the range
                for ($date = Carbon::parse($startDate); $date->lte(Carbon::parse($endDate)); $date->addDay()) {
                    $dateString = $date->format('Y-m-d');

                    // Check if there's any attendance for the current date
                    if ($daysAttendance->has($dateString)) {
                        $attendanceTimes = $daysAttendance->get($dateString);

                        // Take the first and last punch time for each day
                        $firstPunch = $attendanceTimes->first()->punch_time;
                        $lastPunch = $attendanceTimes->last()->punch_time;
                        $status = $this->determinePresentStatus($firstPunch, $lastPunch, $dateString);

                        // Calculate working hours and overtime
                        $overtime = $this->overtimeCalc($firstPunch, $lastPunch);
                        $overtimeHours = 0;
                        if ($overtime > 0){
                            $overtimeHours = $this->overtimeCalcHours($overtime);
                        }

                        $reportData->push([
                            'sl' => $employee->id,
                            'id_no' => $employee->id_no,
                            'employee_name' => $employee->name,
                            'designation' => $employee->designation->name ?? '',
                            'punch_date' => Carbon::parse($firstPunch)->format('d-m-Y'),
                            'punch_in_time' => Carbon::parse($firstPunch)->format('h:i:s a'),
                            'punch_out_time' => $this->checkCheckOut($firstPunch, $lastPunch),
                            'status' => $status['message'],
                            'overtime' => $overtimeHours,
                            'overtime_in_seconds' => $overtime,
                        ]);
                    } else {
                        // No attendance record for this date; mark as Absent or other
                        $status = $this->determineAbsentStatus($employee->id, $date);
                        $reportData->push([
                            'sl' => $employee->id,
                            'id_no' => $employee->id_no,
                            'employee_name' => $employee->name,
                            'designation' => $employee->designation->name ?? '',
                            'punch_date' => $date->format('d-m-Y'),
                            'punch_in_time' => 'N/A',
                            'punch_out_time' => 'N/A',
                            'status' => $status['message'],
                            'overtime' => '',
                            'overtime_in_seconds' => 0,
                        ]);
                    }
                }

                return $reportData;
            })->flatten(1); // Flatten the collection to avoid nested arrays
        }

        return view('hrm.report.attendance_sheet', compact('employees',
            'report', 'selectEmployee'));
    }

    public function dailyAttendanceSheet(Request $request)
    {
        $report = [];
        $selectEmployee = null;
        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->get();

        $employeeId = $request->employee;
        // Default to branch business_date if no date is provided
        $businessDate = SisterConcern::find(auth()->user()->activeConcernId())?->business_date ?? now()->format('Y-m-d');
        $targetDate = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::parse($businessDate)->format('Y-m-d');

        $attendanceQuery = Employee::with(['attendances' => function ($query) use ($targetDate) {
            $query->whereDate('punch_time', $targetDate)
                ->orderBy('punch_time', 'asc');
        }]);

        if ($employeeId != '') {
            $selectEmployee = Employee::find($employeeId);
            $attendanceQuery->where('id', $employeeId);
        } else {
            $attendanceQuery->where('sister_concern_id', auth()->user()->activeConcernId());
        }

        $employeeRecords = $attendanceQuery->get();

        $reportData = collect();
        $date = Carbon::parse($targetDate);
        $dateString = $targetDate;

        foreach ($employeeRecords as $employee) {
            if ($employee->attendances->isNotEmpty()) {
                $attendanceTimes = $employee->attendances;
                $firstPunch = $attendanceTimes->first()->punch_time;
                $lastPunch = $attendanceTimes->last()->punch_time;
                $status = $this->determinePresentStatus($firstPunch, $lastPunch, $dateString);
                $overtime = $this->overtimeCalc($firstPunch, $lastPunch);
                $overtimeHours = 0;
                if ($overtime > 0) {
                    $overtimeHours = $this->overtimeCalcHours($overtime);
                }

                $reportData->push([
                    'sl' => $employee->id,
                    'id_no' => $employee->id_no,
                    'employee_name' => $employee->name,
                    'designation' => $employee->designation->name ?? '',
                    'punch_date' => Carbon::parse($firstPunch)->format('d-m-Y'),
                    'punch_in_time' => Carbon::parse($firstPunch)->format('h:i:s a'),
                    'punch_out_time' => $this->checkCheckOut($firstPunch, $lastPunch),
                    'status' => $status['message'],
                    'overtime' => $overtimeHours,
                    'overtime_in_seconds' => $overtime,
                ]);
            } else {
                $status = $this->determineAbsentStatus($employee->id, $dateString);
                $reportData->push([
                    'sl' => $employee->id,
                    'id_no' => $employee->id_no,
                    'employee_name' => $employee->name,
                    'designation' => $employee->designation->name ?? '',
                    'punch_date' => $date->format('d-m-Y'),
                    'punch_in_time' => 'N/A',
                    'punch_out_time' => 'N/A',
                    'status' => $status['message'],
                    'overtime' => '',
                    'overtime_in_seconds' => 0,
                ]);
            }
        }

        $report = $reportData;

        // Set request date for view if it was empty so the blade defaults to business date
        if (!$request->has('date')) {
            $request->merge(['date' => Carbon::parse($businessDate)->format('d-m-Y')]);
        }

        return view('hrm.report.daily_attendance_sheet', compact('employees', 'report', 'selectEmployee'));
    }

    public function attendanceSummaryFunction($employee,$startDate,$endDate)
    {
        // Sample usage
        $attendanceSummary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'no_checkout' => 0,
            'early_checkout' => 0,
            'late_and_early_checkout'=> 0,
            'movement' => 0,
            'public_holiday' => 0,
            'weekend_holiday' => 0,
            'leave' => 0,
            'overtime' => 0,
        ];
        // Group the attendance records by date
        $daysAttendance = $employee->attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->punch_time)->format('Y-m-d');
        });

        // Loop through each date in the range
        for ($date = Carbon::parse($startDate); $date->lte(Carbon::parse($endDate)); $date->addDay()) {
            $dateString = $date->format('Y-m-d');

            // Determine the status for the current date
            if ($daysAttendance->has($dateString)) {
                $attendanceTimes = $daysAttendance->get($dateString);
                $firstPunch = $attendanceTimes->first()->punch_time;
                $lastPunch = $attendanceTimes->last()->punch_time;

                // Determine the status (Present, Late, Movement, etc.)
                $status = $this->determinePresentStatus($firstPunch, $lastPunch, $dateString);
                $overtime = $this->overtimeCalc($firstPunch, $lastPunch);
                $attendanceSummary['overtime'] = $attendanceSummary['overtime'] + $overtime;
            } else {
                // If no attendance record for the date, determine if it's a holiday, leave, etc.
                $status = $this->determineAbsentStatus($employee->id, $dateString);
            }
            // Increment the appropriate counter in the summary
            if ($status['status'] == 'present') {
                $attendanceSummary['present']++;
            } elseif ($status['status'] == 'absent') {
                $attendanceSummary['absent']++;
            } elseif ($status['status'] == 'late') {
                $attendanceSummary['late']++;
            } elseif ($status['status'] == 'no_checkout') {
                $attendanceSummary['no_checkout']++;
            } elseif ($status['status'] == 'early_checkout') {
                $attendanceSummary['early_checkout']++;
            } elseif ($status['status'] == 'late_and_early_checkout') {
                $attendanceSummary['late_and_early_checkout']++;
            } elseif ($status['status'] == 'movement') {
                $attendanceSummary['movement']++;
            } elseif ($status['status'] == 'public_holiday') {
                $attendanceSummary['public_holiday']++;
            } elseif ($status['status'] == 'weekend_holiday') {
                $attendanceSummary['weekend_holiday']++;
            } elseif ($status['status'] == 'leave') {
                $attendanceSummary['leave']++;
            }
        }

        return $attendanceSummary;
    }
    public function attendanceSummary(Request $request)
    {
        $employees = Employee::where('sister_concern_id',auth()->user()->activeConcernId())->get();
        $employeeSummaries = [];


        if ($request->start_date != '' && $request->end_date != '') {
            $employeeId = $request->employee;
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            // Fetch attendance data for the employee within the date range
            $attendanceEmployeeQuery = Employee::with(['attendances' => function ($query) use ($startDate, $endDate) {
                 $query->whereDate('punch_time', '>=', $startDate)
                         ->whereDate('punch_time', '<=', $endDate)
                    ->orderBy('punch_time', 'asc');
            }]);

            if ($employeeId != '') {
                $attendanceEmployeeQuery->where('id', $employeeId);
            }

            $attendanceEmployees = $attendanceEmployeeQuery->get();


            // Loop through each employee
            foreach ($attendanceEmployees as $employee) {
                $summary = $this->attendanceSummaryFunction($employee,$startDate,$endDate);
                // Store the summary for this employee
                $employeeSummaries[] = [
                    'employee' => $employee,
                    'summary' => $summary,
                ];
            }

        }

        return view('hrm.report.attendance_summary', compact('employees',
        'employeeSummaries'));
    }

    public function reminderReport(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        // Resolve any still-pending reminders whose window has closed
        \App\Models\Hrm\AttendanceReminderLog::where('status', 'pending')
            ->where('window_closes_at', '<', Carbon::now())
            ->update(['status' => 'missed']);

        $logs = \App\Models\Hrm\AttendanceReminderLog::with('employee.designation')
            ->whereDate('notified_at', $date)
            ->get()
            ->groupBy('employee_id');

        $report = $logs->map(function ($employeeLogs) {
            $employee = $employeeLogs->first()->employee;
            return [
                'employee'     => $employee,
                'total_sent'   => $employeeLogs->count(),
                'responded'    => $employeeLogs->where('status', 'responded')->count(),
                'missed'       => $employeeLogs->where('status', 'missed')->count(),
                'pending'      => $employeeLogs->where('status', 'pending')->count(),
            ];
        })->values();

        return view('hrm.report.reminder_report', compact('report', 'date'));
    }

}