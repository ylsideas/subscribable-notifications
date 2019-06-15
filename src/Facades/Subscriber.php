<?php

namespace YlsIdeas\SubscribableNotifications\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;

/**
 * Class Subscriber
 * @package YlsIdeas\SubscribableNotifications\Facades
 *
 * @method static void routes()
 * @method static string routeName()
 * @method static userModel(string $model = null)
 * @method static void onCompletion(\Closure $handler)
 * @method static void onUnsubscribeFromMailingList(\Closure $handler)
 * @method static void onUnsubscribeFromAllMailingLists(\Closure $handler)
 * @method static void unsubscribeFromMailingList($user, string $mailingList)
 * @method static void unsubscribeFromAllMailingLists($user)
 * @method static Response complete($user, ?string $mailingList = null)
 */
class Subscriber extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \YlsIdeas\SubscribableNotifications\Subscriber::class;
    }
}