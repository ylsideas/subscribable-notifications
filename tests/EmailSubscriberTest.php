<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;

/**
 * Class EmailSubscriberTest.
 */
class EmailSubscriberTest extends TestCase
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
        $user = DummyUser::create([
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
        $user = DummyUser::create([
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
}
