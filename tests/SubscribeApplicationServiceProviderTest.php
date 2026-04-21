<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyApplicationServiceProvider;

class SubscribeApplicationServiceProviderTest extends TestCase
{
    public function test_it_can_be_configured_to_loads_routes()
    {
        Subscriber::shouldReceive('routes');
        Subscriber::shouldReceive('userModel');
        Subscriber::shouldReceive('onUnsubscribeFromMailingList');
        Subscriber::shouldReceive('onUnsubscribeFromAllMailingLists');
        Subscriber::shouldReceive('onCompletion');
        Subscriber::shouldReceive('onCheckSubscriptionStatusOfMailingList');
        Subscriber::shouldReceive('onCheckSubscriptionStatusOfAllMailingLists');
        $provider = new DummyApplicationServiceProvider($this->app);

        $provider->shouldLoadRoutes(true);

        $provider->boot();
    }

    public function test_it_can_be_configured_to_not_loads_routes()
    {
        Subscriber::shouldReceive('routes')->never();
        Subscriber::shouldReceive('userModel');
        Subscriber::shouldReceive('onUnsubscribeFromMailingList');
        Subscriber::shouldReceive('onUnsubscribeFromAllMailingLists');
        Subscriber::shouldReceive('onCompletion');
        Subscriber::shouldReceive('onCheckSubscriptionStatusOfMailingList');
        Subscriber::shouldReceive('onCheckSubscriptionStatusOfAllMailingLists');
        $provider = new DummyApplicationServiceProvider($this->app);

        $provider->shouldLoadRoutes(false);

        $provider->boot();
    }
}
