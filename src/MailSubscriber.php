<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

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
    public function mailSubscriptionStatus(Notification $notification): bool
    {
        return Subscriber::checkSubscriptionStatus(
            $this,
            $notification instanceof AppliesToMailingList
                ? $notification->usesMailingList()
                : null
        );
    }
}
