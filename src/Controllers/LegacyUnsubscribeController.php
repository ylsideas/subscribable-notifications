<?php

namespace YlsIdeas\SubscribableNotifications\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YlsIdeas\SubscribableNotifications\Contracts\SubscriberContract;

final class LegacyUnsubscribeController extends Controller
{
    public function __construct(private readonly SubscriberContract $subscriber)
    {
        $this->middleware('signed');
    }

    public function __invoke(Request $request, mixed $subscriberId, ?string $mailingList = null): mixed
    {
        return app(UnsubscribeController::class)(
            $request,
            $this->subscriber->getLegacySubscriberType(),
            $subscriberId,
            $mailingList
        );
    }
}
