<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribed;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribing;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyApplicationServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser;

/**
 * Class UnsubscribeControllerTest.
 *
 * @covers \YlsIdeas\SubscribableNotifications\Controllers\UnsubscribeController
 */
class UnsubscribeControllerTest extends TestCase
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
            DummyApplicationServiceProvider::class,
        ];
    }

    public function test_it_unsubscribes_users_from_all_mailing_lists()
    {
        $this->withoutExceptionHandling();

        $expected = false;

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        Subscriber::onUnsubscribeFromAllMailingLists(
            function ($user) use (&$expected, $expectedUser) {
                $expected = true;
                $this->assertEquals($expectedUser->id, $user->id);
            }
        );

        $this->get($expectedUser->unsubscribeLink())
            ->assertStatus(302)
            ->assertRedirect('/');

        $this->assertTrue($expected);
    }

    public function test_it_unsubscribes_users_from_a_mailing_list()
    {
        $this->withoutExceptionHandling();

        $expected = false;

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        Subscriber::onUnsubscribeFromMailingList(
            function ($user, $mailingList) use (&$expected, $expectedUser) {
                $expected = true;
                $this->assertEquals($expectedUser->id, $user->id);
                $this->assertEquals('test', $mailingList);
            }
        );

        $this->get($expectedUser->unsubscribeLink('test'))
            ->assertStatus(302)
            ->assertRedirect('/');

        $this->assertTrue($expected);
    }

    public function test_it_uses_the_subscriber_to_redirect_the_user_after_completion()
    {
        $this->withoutExceptionHandling();

        $expected = false;

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        Subscriber::onCompletion(
            function ($user, $mailingList) use (&$expected, $expectedUser) {
                $expected = true;
                $this->assertEquals($expectedUser->id, $user->id);
                $this->assertEquals('test', $mailingList);

                return new Response('test');
            }
        );

        $this->get($expectedUser->unsubscribeLink('test'))
            ->assertOk()
            ->assertSee('test');

        $this->assertTrue($expected);
    }

    public function test_it_aborts_if_the_target_model_does_not_exist()
    {
        $notExpected = false;

        Subscriber::onCompletion(
            function () use (&$notExpected) {
                $notExpected = true;
            }
        );

        $this->get(
            URL::signedRoute(
                Subscriber::routeName(),
                [
                    'subscriberType' => DummyUser::class,
                    'subscriberId' => 999,
                    'mailingList' => 'test',
                ]
            )
        )
            ->assertStatus(403);

        $this->assertFalse($notExpected);
    }

    public function test_it_fires_events_for_unsubscribing()
    {
        $this->withoutExceptionHandling();

        Event::fake();

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $this->get($expectedUser->unsubscribeLink())
            ->assertStatus(302)
            ->assertRedirect('/');

        Event::assertDispatched(UserUnsubscribing::class);
        Event::assertDispatched(UserUnsubscribed::class);
    }

    public function test_it_returns_200_for_rfc8058_one_click_post_unsubscribe()
    {
        $this->withoutExceptionHandling();

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $called = false;
        Subscriber::onUnsubscribeFromAllMailingLists(function ($user) use (&$called) {
            $called = true;
        });

        $this->post($expectedUser->unsubscribeLink())
            ->assertNoContent();

        $this->assertTrue($called);
    }

    public function test_it_returns_200_for_rfc8058_one_click_post_unsubscribe_from_mailing_list()
    {
        $this->withoutExceptionHandling();

        /** @var DummyUser $user */
        $expectedUser = DummyUser::create([
            'name' => 'test',
            'email' => 'test@testing.local',
            'password' => 'test',
        ]);

        $called = false;
        Subscriber::onUnsubscribeFromMailingList(function ($user, $list) use (&$called) {
            $called = true;
            $this->assertEquals('newsletter', $list);
        });

        $this->post($expectedUser->unsubscribeLink('newsletter'))
            ->assertNoContent();

        $this->assertTrue($called);
    }
}
