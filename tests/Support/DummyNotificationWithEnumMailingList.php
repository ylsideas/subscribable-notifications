<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;

class DummyNotificationWithEnumMailingList extends Notification implements AppliesToMailingList
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): SubscribableMailMessage
    {
        return SubscribableMailMessage::via($notifiable, $this)
            ->line('The introduction to the notification.');
    }

    public function usesMailingList(): string|\BackedEnum
    {
        return DummyMailingList::Newsletter;
    }
}
