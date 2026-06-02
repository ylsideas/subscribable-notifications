<?php

namespace YlsIdeas\SubscribableNotifications\Events;

final class UserUnsubscribing
{
    public function __construct(
        public readonly object $user,
        public readonly ?string $mailingList = null,
    ) {
    }
}
