<?php

namespace YlsIdeas\SubscribableNotifications\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use YlsIdeas\SubscribableNotifications\Testing\FakeSubscriber;

/**
 * Class Subscriber.
 *
 * @see \YlsIdeas\SubscribableNotifications\Subscriber
 *
 * @method static void routes(mixed $router = null, string|false $throttle = '60,1')
 * @method static void legacyRoutes(string $defaultModel, mixed $router = null, string|false $throttle = '60,1')
 * @method static string routeName()
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
    protected static function getFacadeAccessor(): string
    {
        return \YlsIdeas\SubscribableNotifications\Subscriber::class;
    }

    public static function fake(): FakeSubscriber
    {
        $fake = new FakeSubscriber();
        static::swap($fake);

        return $fake;
    }
}
