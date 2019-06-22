<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Http\Response;

class DummySubscriptionHandler
{
    public function processUnsubscription($user, $mailingList = null)
    {
    }

    public function processCompletion($user, $mailingList = null)
    {
        return new Response();
    }
}
