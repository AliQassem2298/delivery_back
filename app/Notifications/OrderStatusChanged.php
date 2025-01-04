<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class OrderStatusChanged extends Notification
{
    use Queueable;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
      $this->message=$message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'time' => now(),
        ];
    }

}
