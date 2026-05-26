<?php

namespace YlsIdeas\SubscribableNotifications\Controllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribed;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribing;
use YlsIdeas\SubscribableNotifications\Subscriber;

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
     * @param string $subscriberType
     * @param mixed $subscriberId
     * @param string|null $mailingList
     * @return Response
     */
    public function __invoke(Request $request, string $subscriberType, $subscriberId, ?string $mailingList = null)
    {
        $modelClass = Relation::getMorphedModel($subscriberType) ?? $subscriberType;

        if (! class_exists($modelClass) || ! is_a($modelClass, CanUnsubscribe::class, true)) {
            abort(403, __('Could not process unsubscribe request'));
        }

        $model = new $modelClass();

        $subscriber = $model
            ->where($model->getRouteKeyName(), $subscriberId)
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

        // RFC 8058: one-click POST must not redirect; return 204 No Content
        if ($request->isMethod('post')) {
            return response('', 204);
        }

        return $this->subscriber->complete($subscriber, $mailingList);
    }
}
