<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Support\ServiceProvider;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

class DummyApplicationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Subscriber::routes();

        Subscriber::onUnsubscribeFromMailingList(function () {
        });

        Subscriber::onUnsubscribeFromAllMailingLists(function () {
        });

        Subscriber::onCompletion(function () {
            return redirect('/');
        });

        Subscriber::onCheckSubscriptionStatusOfMailingList(function () {
            return true;
        });

        Subscriber::onCheckSubscriptionStatusOfAllMailingLists(function () {
            return true;
        });
    }
}
