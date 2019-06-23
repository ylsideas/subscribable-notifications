<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use YlsIdeas\SubscribableNotifications\SubscribableApplicationServiceProvider;

class DummyApplicationServiceProvider extends SubscribableApplicationServiceProvider
{
    protected $model = DummyUser::class;

    protected $loadRoutes = true;

    public function shouldLoadRoutes($shouldLoad = false)
    {
        $this->loadRoutes = $shouldLoad;
    }

    /**
     * @return \Closure
     */
    public function onUnsubscribeFromMailingList()
    {
        return function () {
        };
    }

    /**
     * @return \Closure
     */
    public function onUnsubscribeFromAllMailingLists()
    {
        return function () {
        };
    }

    /**
     * @return \Closure
     */
    public function onCompletion()
    {
        return function () {
            return response()
                ->redirectTo('/');
        };
    }

    /**
     * @return callable|string
     */
    public function onCheckSubscriptionStatusOfMailingList()
    {
        return function () {
            return true;
        };
    }

    /**
     * @return callable|string
     */
    public function onCheckSubscriptionStatusOfAllMailingLists()
    {
        return function () {
            return true;
        };
    }
}
