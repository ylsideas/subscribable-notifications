<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

use Illuminate\Notifications\Notification;

interface CheckSubscriptionStatusBeforeSendingNotifications
{
    public function mailSubscriptionStatus(Notification $notification) : bool;
}