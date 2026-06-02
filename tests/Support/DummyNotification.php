<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;

class DummyNotification extends Notification
{
    public $useView = null;

    public $useMailable = false;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->useView !== null) {
            return (new MailMessage())
                ->view($this->useView)
                ->line('The introduction to the notification.')
                ->action('Notification Action', url('/'))
                ->line('Thank you for using our application!');
        }

        if ($this->useMailable === true) {
            return new DummyMailable();
        }

        $message = $notifiable instanceof CanUnsubscribe
            ? SubscribableMailMessage::via($notifiable, $this)
            : new MailMessage();

        return $message
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}
