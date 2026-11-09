<?php

namespace App\Notifications\Hrm;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeAttendanceReminder extends Notification
{
    use Queueable;

    public $type;

    public function __construct($type = 'hourly_missing')
    {
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        $setting = \App\Models\Hrm\HrmSetting::first();
        $channels = [];

        if (!$setting || $setting->is_web_notification_enabled) {
            $channels[] = 'database';
        }

        if (!$setting || $setting->is_browser_notification_enabled || $setting->is_push_notification_enabled) {
            $channels[] = \App\Channels\FirebaseChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->type === 'first_punch_missing' 
            ? 'Your shift has started, but you have not given your first attendance today. Please update your attendance immediately.'
            : 'It has been over an hour since your last attendance punch. Please update your attendance.';

        return [
            'title' => 'Attendance Reminder',
            'message' => $message,
            'content' => $message,
            'type' => $this->type,
            'url' => route('employees.attendance')
        ];
    }

    public function toFirebase($notifiable)
    {
        $message = $this->type === 'first_punch_missing' 
            ? 'Your shift has started, but you have not given your first attendance today. Please update your attendance immediately.'
            : 'It has been over an hour since your last attendance punch. Please update your attendance.';

        return [
            'title' => 'Attendance Reminder',
            'body' => $message,
            'data' => [
                'type' => $this->type,
                'url' => route('employees.attendance')
            ]
        ];
    }
}
