<?php

namespace YlsIdeas\SubscribableNotifications\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\SubscriberContract;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribed;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribing;

final class UnsubscribeController extends Controller
{
    public function __construct(private readonly SubscriberContract $subscriber)
    {
        $this->middleware('signed');
    }

    public function __invoke(Request $request, string $subscriberType, mixed $subscriberId, ?string $mailingList = null): mixed
    {
        $modelClass = Relation::getMorphedModel($subscriberType) ?? $subscriberType;

        if (! class_exists($modelClass)
            || ! is_a($modelClass, CanUnsubscribe::class, true)
            || ! is_a($modelClass, Model::class, true)) {
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

        if ($request->isMethod('post')) {
            if ($request->input('List-Unsubscribe') !== 'One-Click') {
                abort(400, __('Invalid unsubscribe request'));
            }

            return response('', 204);
        }

        return $this->subscriber->complete($subscriber, $mailingList);
    }
}
