<?php

namespace App\Notifications;

use App\Models\OfficeManagement\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoticePosted extends Notification implements ShouldQueue
{
    use Queueable;

    public $notice;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Notice $notice
     * @return void
     */
    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Notice: ' . $this->notice->title)
            ->greeting('Hello,')
            ->line('A new notice has been posted: ' . $this->notice->title)
            ->line('Content:')
            ->line(strip_tags($this->notice->content)) // Strip HTML for plain text emails
            ->action('View Notice', route('notices.show',['notice'=>$this->notice->id]))
            ->line('Thank you for your attention!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->notice->title,
            'content' => $this->notice->content,
            'url' => route('notices.show',['notice'=>$this->notice->id]),
        ];
    }
}
