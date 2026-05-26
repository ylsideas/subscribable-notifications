<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;

class DummyNotificationWithEnumMailingList extends Notification implements AppliesToMailingList
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->line('The introduction to the notification.');
    }

    public function usesMailingList(): string|\BackedEnum
    {
        return DummyMailingList::Newsletter;
    }
}
