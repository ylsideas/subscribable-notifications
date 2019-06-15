<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Support\Facades\URL;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

trait MailSubscriber
{
    /**
     * @param string|null $mailingList
     * @return string
     */
    public function unsubscribeLink(?string $mailingList = null): string
    {
        return URL::signedRoute(
            Subscriber::routeName(),
            ['subscriber' => $this, 'mailingList' => $mailingList]
        );
    }
}