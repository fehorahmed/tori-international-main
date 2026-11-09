<?php

namespace App\Notifications\Hrm;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAttendanceAlert extends Notification
{
    use Queueable;

    public $employee;
    public $minutesMissed;
    public $type;

    public function __construct($employee, $minutesMissed, $type = 'hourly_missing')
    {
        $this->employee = $employee;
        $this->minutesMissed = $minutesMissed;
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $hours = floor($this->minutesMissed / 60);
        $mins = $this->minutesMissed % 60;
        $timeStr = ($hours > 0 ? "{$hours}h " : "") . "{$mins}m";

        if ($this->type === 'first_punch_missing') {
            $title = "⏳ Not Checked-In ({$timeStr}): {$this->employee->name}";
            $message = "Employee {$this->employee->name} (ID: {$this->employee->id_no}) is late by $timeStr and has not given first attendance today.";
        } else {
            $title = "📍 No Update ({$timeStr}): {$this->employee->name}";
            $message = "Employee {$this->employee->name} (ID: {$this->employee->id_no}) has not updated attendance for $timeStr.";
        }

        return [
            'title' => $title,
            'message' => $message,
            'content' => $message,
            'employee_id' => $this->employee->id,
            'type' => $this->type,
            'url' => route('employees.show', $this->employee->id, false)
        ];
    }
}
