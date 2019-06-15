<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

interface CanUnsubscribe
{
    public function unsubscribeLink(?string $service = null): string;
}
