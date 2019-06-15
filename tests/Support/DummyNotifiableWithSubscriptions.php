<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notifiable;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;

class DummyNotifiableWithSubscriptions implements CanUnsubscribe
{
    use Notifiable;

    protected $email = 'test@testing.local';

    public function unsubscribeLink($service = null): string
    {
        return $service
            ? 'https://testing.local/unsubscribe/' . $service
            : 'https://testing.local/unsubscribe';
    }
}