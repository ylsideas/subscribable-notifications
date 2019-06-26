<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;

trait MailSubscriber
{
    /**
     * @param string|null $mailingList
     * @return string
     */
    public function unsubscribeLink(?string $mailingList = ''): string
    {
        return URL::signedRoute(
            Subscriber::routeName(),
            ['subscriber' => $this, 'mailingList' => $mailingList]
        );
    }

    /**
     * @param Notification $notification
     * @return bool
     */
    public function mailSubscriptionStatus(Notification $notification) : bool
    {
        return Subscriber::checkSubscriptionStatus(
            $this,
            $notification instanceof AppliesToMailingList
                ? $notification->usesMailingList()
                : null
        );
    }
}
