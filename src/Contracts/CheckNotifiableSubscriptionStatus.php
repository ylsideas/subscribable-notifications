<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

interface CheckNotifiableSubscriptionStatus
{
    public function checkMailSubscriptionStatus() : bool;
}