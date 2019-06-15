<?php

namespace YlsIdeas\SubscribableNotifications\Events;

class UserUnsubscribing
{
    /**
     * @var Authenticatable
     */
    public $user;
    /**
     * @var string|null
     */
    public $mailingList;

    public function __construct($user, ?string $mailingList = null)
    {
        $this->user = $user;
        $this->mailingList = $mailingList;
    }
}