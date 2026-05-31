<?php

namespace YlsIdeas\SubscribableNotifications\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YlsIdeas\SubscribableNotifications\Subscriber;

class LegacyUnsubscribeController extends Controller
{
    public function __construct(private readonly Subscriber $subscriber)
    {
        $this->middleware('signed');
    }

    public function __invoke(Request $request, $subscriberId, ?string $mailingList = null)
    {
        return app(UnsubscribeController::class)(
            $request,
            $this->subscriber->legacySubscriberType,
            $subscriberId,
            $mailingList
        );
    }
}
