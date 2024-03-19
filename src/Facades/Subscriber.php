<?php

namespace YlsIdeas\SubscribableNotifications\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Class Subscriber.
 *
 * @see \YlsIdeas\SubscribableNotifications\Subscriber
 *
 * @method static void routes()
 * @method static string routeName()
 * @method static mixed userModel(string $model = null)
 * @method static void onCompletion(callable|string $handler)
 * @method static void onUnsubscribeFromMailingList(callable|string $handler)
 * @method static void onUnsubscribeFromAllMailingLists(callable|string $handler)
 * @method static void onCheckSubscriptionStatusOfAllMailingLists(callable|string $handler)
 * @method static void onCheckSubscriptionStatusOfMailingList(callable|string $handler)
 * @method static void unsubscribeFromMailingList($user, string $mailingList)
 * @method static void unsubscribeFromAllMailingLists($user)
 * @method static Response complete($user, ?string $mailingList = null)
 * @method static bool checkSubscriptionStatus($user, ?string $mailingList = null)
 */
class Subscriber extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \YlsIdeas\SubscribableNotifications\Subscriber::class;
    }
}
