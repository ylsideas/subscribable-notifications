<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;

class DummyNotificationWithMailingList extends Notification implements AppliesToMailingList
{
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
        return (new MailMessage())
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * @return string
     */
    public function usesMailingList(): string
    {
        return 'testing-list';
    }
}
