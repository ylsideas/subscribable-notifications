<?php

namespace YlsIdeas\SubscribableNotifications\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Subscriber;

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
    public function it_handles_unsubscribing_from_all_mailing_lists_via_closure()
    {
        $subscriber = new Subscriber($this->app);

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
    public function it_handles_unsubscribing_from_all_mailing_lists_via_string()
    {
        $subscriber = new Subscriber($this->app);
        $dummy = \Mockery::mock();

        $expectedUser = new User();

        $dummy->shouldReceive('processUnsubscription')
            ->with($expectedUser)
            ->once();

        $this->app->singleton('\DummyClass', function () use ($dummy) {
            return $dummy;
        });

        $subscriber->onUnsubscribeFromAllMailingLists(
            '\DummyClass@processUnsubscription'
        );

        $subscriber->unsubscribeFromAllMailingLists($expectedUser);
    }

    /** @test */
    public function it_handles_unsubscribing_from_a_mailing_list_via_closure()
    {
        $subscriber = new Subscriber($this->app);

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
    public function it_handles_unsubscribing_from_a_mailing_list_via_string()
    {
        $subscriber = new Subscriber($this->app);
        $dummy = \Mockery::mock();

        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $dummy->shouldReceive('processUnsubscription')
            ->with($expectedUser, $expectedMailingList)
            ->once();

        $this->app->singleton('\DummyClass', function () use ($dummy) {
            return $dummy;
        });

        $subscriber->onUnsubscribeFromMailingList(
            '\DummyClass@processUnsubscription'
        );

        $subscriber->unsubscribeFromMailingList($expectedUser, $expectedMailingList);
    }

    /** @test */
    public function it_handles_generating_a_response_for_unsubscribing_via_closure()
    {
        $subscriber = new Subscriber($this->app);

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
    public function it_handles_generating_a_response_for_unsubscribing_via_string()
    {
        $subscriber = new Subscriber($this->app);
        $dummy = \Mockery::mock();

        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $dummy->shouldReceive('processCompletion')
            ->with($expectedUser, $expectedMailingList)
            ->andReturn(new Response())
            ->once();

        $this->app->singleton('\DummyClass', function () use ($dummy) {
            return $dummy;
        });

        $subscriber->onCompletion(
            '\DummyClass@processCompletion'
        );

        $response = $subscriber->complete($expectedUser, $expectedMailingList);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    public function it_handles_checking_subscription_status_of_a_mailing_list_via_closure()
    {
        $subscriber = new Subscriber($this->app);

        $expectedFirst = false;
        $expectedSecond = false;
        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $subscriber->onCheckSubscriptionStatusOfAllMailingLists(
            function ($user) use (&$expectedFirst, $expectedUser) {
                $expectedFirst = true;
                $this->assertInstanceOf(User::class, $user);
                $this->assertSame($expectedUser, $user);

                return true;
            }
        );

        $subscriber->onCheckSubscriptionStatusOfMailingList(
            function ($user, $mailingList) use (&$expectedSecond, $expectedUser, $expectedMailingList) {
                $expectedSecond = true;
                $this->assertInstanceOf(User::class, $user);
                $this->assertSame($expectedUser, $user);
                $this->assertSame($expectedMailingList, $mailingList);

                return true;
            }
        );

        $this->assertTrue($subscriber->checkSubscriptionStatus($expectedUser, $expectedMailingList));

        $this->assertTrue($expectedFirst);
        $this->assertTrue($expectedSecond);
    }

    /** @test */
    public function it_handles_checking_subscription_status_of_a_mailing_list_via_string()
    {
        $subscriber = new Subscriber($this->app);
        $dummy = \Mockery::mock();

        $expectedUser = new User();
        $expectedMailingList = 'testing-list';

        $dummy->shouldReceive('getSubscriptionStatus')
            ->with($expectedUser)
            ->andReturn(true)
            ->once();

        $dummy->shouldReceive('getSubscriptionStatus')
            ->with($expectedUser, $expectedMailingList)
            ->andReturn(true)
            ->once();

        $this->app->singleton('\DummyClass', function () use ($dummy) {
            return $dummy;
        });

        $subscriber->onCheckSubscriptionStatusOfAllMailingLists(
            '\DummyClass@getSubscriptionStatus'
        );

        $subscriber->onCheckSubscriptionStatusOfMailingList(
            '\DummyClass@getSubscriptionStatus'
        );

        $this->assertTrue($subscriber->checkSubscriptionStatus($expectedUser, $expectedMailingList));
    }

    /** @test */
    public function it_handles_checking_subscription_status_of_all_mailing_lists_via_closure()
    {
        $subscriber = new Subscriber($this->app);

        $expected = false;
        $expectedUser = new User();

        $subscriber->onCheckSubscriptionStatusOfAllMailingLists(
            function ($user) use (&$expected, $expectedUser) {
                $expected = true;
                $this->assertInstanceOf(User::class, $user);
                $this->assertSame($expectedUser, $user);

                return true;
            }
        );

        $this->assertTrue($subscriber->checkSubscriptionStatus($expectedUser));
        $this->assertTrue($expected);
    }

    /** @test */
    public function it_handles_checking_subscription_status_of_all_mailing_lists_via_string()
    {
        $subscriber = new Subscriber($this->app);
        $dummy = \Mockery::mock();

        $expectedUser = new User();
        $notExpectedMailingList = 'testing-list';

        $dummy->shouldReceive('getSubscriptionStatus')
            ->with($expectedUser)
            ->andReturn(true)
            ->once();

        $dummy->shouldReceive('getSubscriptionStatus')
            ->with($expectedUser, $notExpectedMailingList)
            ->andReturn(true)
            ->never();

        $this->app->singleton('\DummyClass', function () use ($dummy) {
            return $dummy;
        });

        $subscriber->onCheckSubscriptionStatusOfAllMailingLists(
            '\DummyClass@getSubscriptionStatus'
        );

        $subscriber->onCheckSubscriptionStatusOfMailingList(
            '\DummyClass@getSubscriptionStatus'
        );

        $this->assertTrue($subscriber->checkSubscriptionStatus($expectedUser));
    }

    /** @test */
    public function it_handles_type_checks_for_a_callable_or_string_handler()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Handler argument must be either a string or callable.');

        $subscriber = new Subscriber($this->app);

        $subscriber->onCompletion(
            new \stdClass()
        );
    }

    /** @test */
    public function it_can_provide_a_user_model()
    {
        $subscriber = new Subscriber($this->app);
        $subscriber->userModel = \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class;

        $this->assertEquals(
            \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class,
            $subscriber->userModel()
        );
    }

    /** @test */
    public function it_can_configure_a_user_model()
    {
        $subscriber = new Subscriber($this->app);
        $subscriber->userModel(\YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class);

        $this->assertEquals(
            \YlsIdeas\SubscribableNotifications\Tests\Support\DummyUser::class,
            $subscriber->userModel
        );
    }

    /** @test */
    public function it_can_configure_a_route_for_the_unsubscribe_controller()
    {
        $subscriber = new Subscriber($this->app);

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
