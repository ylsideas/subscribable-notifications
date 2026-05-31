<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

final class Subscriber
{
    public string $uri = 'unsubscribe/{subscriberType}/{subscriberId}/{mailingList?}';
    public string $handler = '\YlsIdeas\SubscribableNotifications\Controllers\UnsubscribeController';
    public string $routeName = 'unsubscribe';

    /** Morph type stored when legacyRoutes() is called — used by LegacyUnsubscribeController. */
    private ?string $legacySubscriberType = null;

    public function getLegacySubscriberType(): ?string
    {
        return $this->legacySubscriberType;
    }

    private ?\Closure $onUnsubscribeFromMailingList = null;
    private ?\Closure $onUnsubscribeFromAllMailingLists = null;
    private ?\Closure $onCompletion = null;
    private ?\Closure $onCheckSubscriptionStatusForMailingLists = null;
    private ?\Closure $onCheckSubscriptionStatusForAllMailingLists = null;

    public function __construct(private readonly Application $app)
    {
    }

    public function routes(mixed $router = null): void
    {
        $router = $router ?? $this->app->make('router');
        $router->match(['GET', 'POST'], $this->uri, $this->handler)
            ->name($this->routeName)
            ->where('subscriberType', '[^\d/][^/]*')
            ->middleware('throttle:60,1');
    }

    public function legacyRoutes(string $defaultModel, mixed $router = null): void
    {
        $router = $router ?? $this->app->make('router');

        $morphMap = Relation::morphMap();
        $this->legacySubscriberType = array_search($defaultModel, $morphMap, true) ?: $defaultModel;

        $router->match(
            ['GET', 'POST'],
            'unsubscribe/{subscriberId}/{mailingList?}',
            '\YlsIdeas\SubscribableNotifications\Controllers\LegacyUnsubscribeController'
        )->name($this->routeName . '.legacy')
            ->middleware('throttle:60,1');
    }

    public function routeName(): string
    {
        return $this->routeName;
    }

    public function onUnsubscribeFromMailingList(string|callable $handler): void
    {
        $this->onUnsubscribeFromMailingList = $this->parseHandler($handler);
    }

    public function onUnsubscribeFromAllMailingLists(string|callable $handler): void
    {
        $this->onUnsubscribeFromAllMailingLists = $this->parseHandler($handler);
    }

    public function onCompletion(string|callable $handler): void
    {
        $this->onCompletion = $this->parseHandler($handler);
    }

    public function onCheckSubscriptionStatusOfAllMailingLists(string|callable $handler): void
    {
        $this->onCheckSubscriptionStatusForAllMailingLists = $this->parseHandler($handler);
    }

    public function onCheckSubscriptionStatusOfMailingList(string|callable $handler): void
    {
        $this->onCheckSubscriptionStatusForMailingLists = $this->parseHandler($handler);
    }

    public function unsubscribeFromMailingList(mixed $user, string $mailingList): void
    {
        ($this->onUnsubscribeFromMailingList)($user, $mailingList);
    }

    public function unsubscribeFromAllMailingLists(mixed $user): void
    {
        ($this->onUnsubscribeFromAllMailingLists)($user);
    }

    public function complete(mixed $user, ?string $mailingList = null): mixed
    {
        return ($this->onCompletion)($user, $mailingList);
    }

    public function checkSubscriptionStatus(mixed $user, ?string $mailingList = null): bool
    {
        if ($mailingList !== null) {
            return (bool) ($this->onCheckSubscriptionStatusForAllMailingLists)($user)
                && (bool) ($this->onCheckSubscriptionStatusForMailingLists)($user, $mailingList);
        }

        return (bool) ($this->onCheckSubscriptionStatusForAllMailingLists)($user);
    }

    private function parseHandler(string|callable $handler): \Closure
    {
        if (is_string($handler)) {
            [$class, $method] = Str::parseCallback($handler, '__invoke');

            return \Closure::fromCallable([$this->app->make($class), $method]);
        }

        return \Closure::fromCallable($handler);
    }
}
