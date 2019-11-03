<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DummyNotification extends Notification
{
    public $useView = null;

    public $useMailable = false;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
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

        return (new MailMessage())
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }
}
