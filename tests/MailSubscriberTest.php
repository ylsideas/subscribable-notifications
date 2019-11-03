<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotification;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithMailingList;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser;

/**
 * Class MailSubscriberTest.
 */
class MailSubscriberTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [
            SubscribableServiceProvider::class,
        ];
    }

    /** @test */
    public function it_generates_a_signed_url_for_users_to_unsubscribe()
    {
        Route::get('unsubscribe/{subscriber}/{mailingList?}', function () {
        })->name('unsubscribe');

        /** @var DummyUser $user */
        $user = DummyUser::make([
            'id' => 1,
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $url = $user->unsubscribeLink();

        $this->assertEquals(
            'http://localhost/unsubscribe/1?signature=bcc7b9f9909fb5f427f1b55022ee44eafee3d655a449f48d2e751748e1a9bcdf',
            $url
        );
    }

    /** @test */
    public function it_generates_a_signed_url_for_users_to_unsubscribe_from_a_mailing_list()
    {
        Route::get('unsubscribe/{subscriber}/{mailingList?}', function () {
        })->name('unsubscribe');

        /** @var DummyUser $user */
        $user = DummyUser::make([
            'id' => 1,
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $url = $user->unsubscribeLink('test');

        $this->assertEquals(
            'http://localhost/unsubscribe/1/test?signature=edaf5bee199875521054535be04273c5006e4d6d834c98a83a3aa16a92223814',
            $url
        );
    }

    /** @test */
    public function it_can_check_its_subscription_status_for_all_mailing_lists()
    {
        /** @var DummyUser $user */
        $user = DummyUser::make([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $notification = new DummyNotification();

        Subscriber::shouldReceive('checkSubscriptionStatus')
            ->with($user, null)
            ->andReturn(true);

        $this->assertTrue($user->mailSubscriptionStatus($notification));
    }

    /** @test */
    public function it_can_check_its_subscription_status_for_one_mailing_list()
    {
        /** @var DummyUser $user */
        $user = DummyUser::make([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $notification = new DummyNotificationWithMailingList();

        Subscriber::shouldReceive('checkSubscriptionStatus')
            ->with($user, 'testing-list')
            ->andReturn(true);

        $this->assertTrue($user->mailSubscriptionStatus($notification));
    }
}
