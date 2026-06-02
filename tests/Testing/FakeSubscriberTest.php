<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Testing;

use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Testing\FakeSubscriber;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyApplicationServiceProvider;

class FakeSubscriberTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SubscribableServiceProvider::class,
            DummyApplicationServiceProvider::class,
        ];
    }

    public function test_fake_returns_a_fake_subscriber_instance()
    {
        $fake = Subscriber::fake();

        $this->assertInstanceOf(FakeSubscriber::class, $fake);
    }

    public function test_fake_swaps_the_underlying_implementation()
    {
        $fake = Subscriber::fake();

        $this->assertSame($fake, Subscriber::getFacadeRoot());
    }

    public function test_it_records_mailing_list_unsubscribes()
    {
        $fake = Subscriber::fake();
        $user = new \stdClass();

        $fake->unsubscribeFromMailingList($user, 'newsletter');

        $fake->assertUnsubscribedFromMailingList($user, 'newsletter');
    }

    public function test_it_records_all_mail_unsubscribes()
    {
        $fake = Subscriber::fake();
        $user = new \stdClass();

        $fake->unsubscribeFromAllMailingLists($user);

        $fake->assertUnsubscribedFromAll($user);
    }

    public function test_assert_nothing_unsubscribed_passes_when_empty()
    {
        $fake = Subscriber::fake();

        $fake->assertNothingUnsubscribed();
    }

    public function test_it_controls_subscription_status()
    {
        $fake = Subscriber::fake();

        $fake->alwaysUnsubscribed();
        $this->assertFalse($fake->checkSubscriptionStatus(new \stdClass(), 'newsletter'));

        $fake->alwaysSubscribed();
        $this->assertTrue($fake->checkSubscriptionStatus(new \stdClass(), 'newsletter'));
    }
}
