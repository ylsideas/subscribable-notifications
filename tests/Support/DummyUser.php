<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Foundation\Auth\User as BaseUser;
use YlsIdeas\SubscribableNotifications\MailSubscriber;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;

class DummyUser extends BaseUser implements CanUnsubscribe, CheckSubscriptionStatusBeforeSendingNotifications
{
    use MailSubscriber;

    protected $table = 'users';
    protected $guarded = [];
}
