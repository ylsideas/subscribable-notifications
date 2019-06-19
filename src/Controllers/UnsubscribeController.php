<?php

namespace YlsIdeas\SubscribableNotifications\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use YlsIdeas\SubscribableNotifications\Subscriber;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribed;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribing;

/**
 * Class UnsubscribeController.
 */
class UnsubscribeController extends Controller
{
    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * UnsubscribeController constructor.
     * @param Subscriber $subscriber
     */
    public function __construct(Subscriber $subscriber)
    {
        $this->middleware('signed');
        $this->subscriber = $subscriber;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     *
     * @param $subscriber
     * @param string|null $mailingList
     * @return Response
     */
    public function __invoke(Request $request, $subscriber, ?string $mailingList = null)
    {
        $model = new $this->subscriber->userModel();

        $subscriber = $model
            ->where($model->getRouteKeyName(), $subscriber)
            ->first();

        if (! $subscriber) {
            abort(403, __('Could not process unsubscribe request'));
        }

        event(new UserUnsubscribing($subscriber, $mailingList));

        if ($mailingList) {
            $this->subscriber->unsubscribeFromMailingList($subscriber, $mailingList);
        } else {
            $this->subscriber->unsubscribeFromAllMailingLists($subscriber);
        }

        event(new UserUnsubscribed($subscriber, $mailingList));

        return $this->subscriber->complete($subscriber, $mailingList);
    }
}
