<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use YlsIdeas\SubscribableNotifications\Subscriber;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;

/**
 * Class SubscriberTest.
 *
 * @covers \YlsIdeas\SubscribableNotifications\Subscriber
 */
class SubscriberTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SubscribableServiceProvider::class,
        ];
    }

    /** @test */
    public function it_handles_unsubscribing_from_all_mailing_lists()
    {
        $subscriber = new Subscriber();

        $expected = false;
        $expectedUser = new User();

        $subscriber->onUnsubscribeFromAllMailingLists(function ($user) use (&$expected, $expectedUser) {
            $expected = true;
            $this->assertInstanceOf(User::class, $user);
            $this->assertSame($expectedUser, $user);
        });

        $subscriber->unsubscribeFromAllMailingLists($expectedUser);

        $this->assertTrue($expected);
    }

    /** @test */
    public function it_handles_unsubscribing_from_a_mailing_list()
    {
        $subscriber = new Subscriber();

        $expected = false;
        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $subscriber->onUnsubscribeFromMailingList(
            function ($user, $mailingList) use (&$expected, $expectedUser, $expectedMailingList) {
                $expected = true;
                $this->assertInstanceOf(User::class, $user);
                $this->assertSame($expectedUser, $user);
                $this->assertSame($expectedMailingList, $mailingList);
            }
        );

        $subscriber->unsubscribeFromMailingList($expectedUser, $expectedMailingList);

        $this->assertTrue($expected);
    }

    /** @test */
    public function it_handles_generating_a_response_for_unsubscribing()
    {
        $subscriber = new Subscriber();

        $expected = false;
        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $subscriber->onCompletion(
            function ($user, $mailingList) use (&$expected, $expectedUser, $expectedMailingList) {
                $expected = true;
                $this->assertInstanceOf(User::class, $user);
                $this->assertSame($expectedUser, $user);
                $this->assertSame($expectedMailingList, $mailingList);

                return new Response();
            }
        );

        $response = $subscriber->complete($expectedUser, $expectedMailingList);
        $this->assertInstanceOf(Response::class, $response);

        $this->assertTrue($expected);
    }

    /** @test */
    public function it_can_provide_a_user_model()
    {
        $subscriber = new Subscriber();
        $subscriber->userModel = \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class;

        $this->assertEquals(
            \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class,
            $subscriber->userModel()
        );
    }

    /** @test */
    public function it_can_configure_a_user_model()
    {
        $subscriber = new Subscriber();
        $subscriber->userModel(\YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class);

        $this->assertEquals(
            \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class,
            $subscriber->userModel
        );
    }

    /** @test */
    public function it_can_configure_a_route_for_the_unsubscribe_controller()
    {
        $subscriber = new Subscriber();

        /** @var Router $router */
        $router = app()->make(Router::class);

        $subscriber->routes($router);
        $router->getRoutes()->refreshNameLookups();

        $this->assertTrue($router->getRoutes()->hasNamedRoute('unsubscribe'));
        /** @var \Illuminate\Routing\Route $route */
        $route = $router->getRoutes()->getByName('unsubscribe');
        $this->assertEquals($route->uri, 'unsubscribe/{subscriber}/{mailingList?}');
    }
}
