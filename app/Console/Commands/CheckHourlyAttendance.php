<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\Hrm\AttendanceReminderService;

#[Signature('attendance:check-hourly')]
#[Description('Check and send hourly attendance reminders to employees')]
class CheckHourlyAttendance extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AttendanceReminderService $service)
    {
        $this->info('Starting hourly attendance check...');
        $service->processHourlyReminders();
        $this->info('Hourly attendance check completed.');
    }
}
