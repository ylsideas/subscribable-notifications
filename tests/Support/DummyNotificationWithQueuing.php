<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;

class DummyNotificationWithQueuing extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): SubscribableMailMessage
    {
        return SubscribableMailMessage::via($notifiable, $this)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}
