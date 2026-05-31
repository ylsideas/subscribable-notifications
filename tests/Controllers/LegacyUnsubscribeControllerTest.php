<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Controllers;

use Illuminate\Support\Facades\URL;
use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyApplicationServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser;

/**
 * @covers \YlsIdeas\SubscribableNotifications\Controllers\LegacyUnsubscribeController
 */
class LegacyUnsubscribeControllerTest extends TestCase
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

    protected function defineEnvironment($app): void
    {
        // Register the legacy route pointing at DummyUser (simulating a v1 User-only setup)
        Subscriber::legacyRoutes(DummyUser::class);
    }

    public function test_it_unsubscribes_via_a_legacy_url_without_subscriber_type()
    {
        $this->withoutExceptionHandling();

        $called = false;
        $user = DummyUser::create(['name' => 'test', 'email' => 'test@testing.local', 'password' => 'test']);

        Subscriber::onUnsubscribeFromAllMailingLists(function ($notifiable) use (&$called, $user) {
            $called = true;
            $this->assertEquals($user->id, $notifiable->id);
        });

        $url = URL::signedRoute('unsubscribe.legacy', ['subscriberId' => $user->id]);

        $this->get($url)->assertStatus(302)->assertRedirect('/');

        $this->assertTrue($called);
    }

    public function test_it_unsubscribes_from_a_mailing_list_via_a_legacy_url()
    {
        $this->withoutExceptionHandling();

        $called = false;
        $user = DummyUser::create(['name' => 'test', 'email' => 'test@testing.local', 'password' => 'test']);

        Subscriber::onUnsubscribeFromMailingList(function ($notifiable, $list) use (&$called, $user) {
            $called = true;
            $this->assertEquals($user->id, $notifiable->id);
            $this->assertEquals('newsletter', $list);
        });

        $url = URL::signedRoute('unsubscribe.legacy', ['subscriberId' => $user->id, 'mailingList' => 'newsletter']);

        $this->get($url)->assertStatus(302)->assertRedirect('/');

        $this->assertTrue($called);
    }

    public function test_it_returns_204_for_rfc8058_post_via_legacy_url()
    {
        $this->withoutExceptionHandling();

        $user = DummyUser::create(['name' => 'test', 'email' => 'test@testing.local', 'password' => 'test']);

        $url = URL::signedRoute('unsubscribe.legacy', ['subscriberId' => $user->id]);

        $this->post($url, ['List-Unsubscribe' => 'One-Click'])->assertNoContent();
    }

    public function test_legacy_url_does_not_interfere_with_new_route()
    {
        $user = DummyUser::create(['name' => 'test', 'email' => 'test@testing.local', 'password' => 'test']);

        // A v2-style URL (subscriberType is a non-numeric string) must hit the new route,
        // not the legacy one
        $newUrl = $user->unsubscribeLink();

        $this->assertStringContainsString('/unsubscribe/', $newUrl);
        $this->assertMatchesRegularExpression('#/unsubscribe/\D[^/]*/[^/]+#', $newUrl);
    }
}
