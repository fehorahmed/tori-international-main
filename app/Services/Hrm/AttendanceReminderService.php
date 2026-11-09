<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Attendance;
use App\Models\Hrm\Employee;
use App\Models\Hrm\AttendanceReminderLog;
use App\Models\Hrm\Holiday;
use App\Models\Hrm\HrmSetting;
use App\Models\Hrm\Leave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\Hrm\EmployeeAttendanceReminder;

class AttendanceReminderService
{
    public function processHourlyReminders()
    {
        Log::info('Checking hourly attendance reminders...');

        $today = Carbon::today()->format('Y-m-d');
        
        $setting = HrmSetting::first();
        $intervalMinutes = $setting ? (int) $setting->notification_interval_minutes : 60;
        $windowMinutes = $setting ? (int) $setting->response_window_minutes : 5;

        // 0. Resolve expired pending reminders as "missed"
        AttendanceReminderLog::where('status', 'pending')
            ->where('window_closes_at', '<', Carbon::now())
            ->update(['status' => 'missed']);

        // 1. Check Weekly Holiday (Assuming Friday is holiday)
        if (Carbon::today()->isFriday()) {
            Log::info('Today is Friday (Weekly Holiday). Skipping attendance check.');
            return;
        }

        // 2. Check Government/Public Holidays
        $isHoliday = Holiday::whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereDate('end_date', '>=', $today)
                      ->orWhereNull('end_date');
            })->exists();

        if ($isHoliday) {
            Log::info('Today is a public holiday. Skipping attendance check.');
            return;
        }

        // Get all active employees
        $employees = Employee::with('shift')->get();

        foreach ($employees as $employee) {
            // 3. Check if employee is on Leave today
            $onLeave = Leave::where('employee_id', $employee->id)
                ->whereIn('status', ['Approved', 'approved', '1']) 
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if ($onLeave) {
                continue; // Skip this employee
            }

            // 3.5 Check if the employee's shift has already ended today
            if ($employee->shift && $employee->shift->end_time) {
                $shiftEnd = Carbon::parse($today . ' ' . $employee->shift->end_time);
                if (Carbon::now()->greaterThan($shiftEnd)) {
                    continue; // Shift is over, no need to send reminders anymore
                }
            }

            // 4. Get their most recent attendance for today
            $lastAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('punch_time', $today)
                ->orderBy('punch_time', 'desc')
                ->first();

            if (!$lastAttendance) {
                // No attendance today. Check if they missed their first punch.
                if ($employee->shift && $employee->shift->start_time) {
                    $shiftStart = Carbon::parse($today . ' ' . $employee->shift->start_time);
                    $minutesLate = $shiftStart->diffInMinutes(Carbon::now(), false);

                    // If current time is >= interval minutes past shift start time
                    if ($minutesLate >= $intervalMinutes) {
                        $this->checkAndSendReminder($employee, $today, 'first_punch_missing', $minutesLate, $intervalMinutes, $windowMinutes);
                    }
                }
                continue;
            }

            // 5. Check if interval time has passed since their last punch time
            $minutesSinceLastPunch = abs(Carbon::now()->diffInMinutes($lastAttendance->punch_time));

            if ($minutesSinceLastPunch >= $intervalMinutes) {
                $this->checkAndSendReminder($employee, $today, 'hourly_missing', $minutesSinceLastPunch, $intervalMinutes, $windowMinutes);
            }
        }
    }

    protected function checkAndSendReminder(Employee $employee, $today, $type, $minutesMissed, $intervalMinutes, $windowMinutes)
    {
        // Check if we already reminded them in the last interval
        $lastReminder = AttendanceReminderLog::where('employee_id', $employee->id)
            ->whereDate('notified_at', $today)
            ->orderBy('notified_at', 'desc')
            ->first();

        if ($lastReminder) {
            $minutesSinceLastReminder = abs(Carbon::now()->diffInMinutes($lastReminder->notified_at));
            if ($minutesSinceLastReminder < $intervalMinutes) {
                return; // Already reminded recently
            }
        }

        // Save the reminder log with dynamic response window
        AttendanceReminderLog::create([
            'employee_id'      => $employee->id,
            'notified_at'      => Carbon::now(),
            'status'           => 'pending',
            'window_closes_at' => Carbon::now()->addMinutes($windowMinutes),
        ]);
 
        // Send Notification to Employee User only (no admin/manager)
        $user = User::where('employee_id', $employee->id)->first();
        if ($user) {
            $user->notify(new EmployeeAttendanceReminder($type));
        }
        
        Log::info("Sent {$type} attendance reminder to Employee ID: {$employee->id}");
    }
}
