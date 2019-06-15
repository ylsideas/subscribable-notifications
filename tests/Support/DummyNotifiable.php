<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Notifications\Notifiable;

class DummyNotifiable
{
    use Notifiable;

    public $email = 'test@testing.local';

    public $shouldRouteNotificationForMail = true;
}