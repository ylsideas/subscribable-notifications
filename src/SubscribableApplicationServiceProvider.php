<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Support\ServiceProvider;

abstract class SubscribableApplicationServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $loadRoutes = true;

    /**
     * @var string
     */
    protected $model = '\App\User';

    public function boot()
    {
        if ($this->loadRoutes === true) {
            $this->loadRoutes();
        }

        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::userModel($this->model);

        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::onUnsubscribeFromMailingList(
            $this->onUnsubscribeFromMailingList()
        );
        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::onUnsubscribeFromAllMailingLists(
            $this->onUnsubscribeFromAllMailingLists()
        );
        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::onCompletion(
            $this->onCompletion()
        );
        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::onCheckSubscriptionStatusOfMailingList(
            $this->onCheckSubscriptionStatusOfMailingList()
        );
        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::onCheckSubscriptionStatusOfAllMailingLists(
            $this->onCheckSubscriptionStatusOfAllMailingLists()
        );
    }

    public function loadRoutes()
    {
        \YlsIdeas\SubscribableNotifications\Facades\Subscriber::routes();
    }

    /**
     * @return callable|string
     */
    abstract public function onUnsubscribeFromMailingList();

    /**
     * @return callable|string
     */
    abstract public function onUnsubscribeFromAllMailingLists();

    /**
     * @return callable|string
     */
    abstract public function onCompletion();

    /**
     * @return callable|string
     */
    abstract public function onCheckSubscriptionStatusOfMailingList();

    /**
     * @return callable|string
     */
    abstract public function onCheckSubscriptionStatusOfAllMailingLists();
}
