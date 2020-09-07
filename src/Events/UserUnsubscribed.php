<?php

namespace YlsIdeas\SubscribableNotifications\Events;

use Illuminate\Foundation\Auth\User;

class UserUnsubscribed
{
    /**
     * @var User
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
