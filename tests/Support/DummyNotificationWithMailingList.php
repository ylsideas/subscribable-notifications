<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus;
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;

class DummyNotificationWithMailingList extends Notification implements AppliesToMailingList, CheckNotifiableSubscriptionStatus
{
    public $shouldCheck = false;

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

    public function usesMailingList(): string
    {
        return 'testing-list';
    }

    public function checkMailSubscriptionStatus(): bool
    {
        return $this->shouldCheck;
    }
}
