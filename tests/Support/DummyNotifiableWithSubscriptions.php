<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;

class DummyNotifiableWithSubscriptions implements CanUnsubscribe, CheckSubscriptionStatusBeforeSendingNotifications
{
    use Notifiable;

    protected $email = 'test@testing.local';

    public $isSubscribed = true;

    public function unsubscribeLink(?string $service = null): string
    {
        return $service
            ? 'https://testing.local/unsubscribe/'.$service
            : 'https://testing.local/unsubscribe';
    }

    public function mailSubscriptionStatus(Notification $notification): bool
    {
        return $this->isSubscribed;
    }
}
