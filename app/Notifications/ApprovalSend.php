<?php

namespace App\Notifications;

use App\Models\OfficeManagement\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalSend extends Notification implements ShouldQueue
{
    use Queueable;

    public $approval;

    /**
     * Create a new notification instance.
     *
     *
     * @return void
     */
    public function __construct($approval)
    {
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // You can use 'mail', 'database', 'broadcast', 'slack', etc.
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        \Log::info('Notification sent for approval', ['title' => $this->approval['title'], 'content' => $this->approval['content']]);

        return [
            'title' => $this->approval['title'],
            'content' => $this->approval['content'],
            'url' => $this->approval['action'],
        ];
    }
}
