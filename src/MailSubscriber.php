<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

trait MailSubscriber
{
    public function unsubscribeLink(?string $mailingList = null): string
    {
        return URL::signedRoute(
            Subscriber::routeName(),
            [
                'subscriberType' => $this->getMorphClass(),
                'subscriberId' => $this->getRouteKey(),
                'mailingList' => $mailingList,
            ]
        );
    }

    public function mailSubscriptionStatus(Notification $notification): bool
    {
        $list = $notification instanceof AppliesToMailingList
            ? $notification->usesMailingList()
            : null;

        return Subscriber::checkSubscriptionStatus(
            $this,
            $list instanceof \BackedEnum ? $list->value : $list
        );
    }
}
