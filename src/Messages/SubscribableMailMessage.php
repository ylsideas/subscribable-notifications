<?php

namespace YlsIdeas\SubscribableNotifications\Messages;

use Illuminate\Notifications\Messages\MailMessage;
use YlsIdeas\SubscribableNotifications\Concerns\SubscribableNotification;

/** @phpstan-consistent-constructor */
class SubscribableMailMessage extends MailMessage
{
    use SubscribableNotification;
}
