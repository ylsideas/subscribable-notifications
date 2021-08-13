<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Channels;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiable;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiableWithSubscriptions;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotification;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithMailingList;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithQueuing;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyTransactionalNotification;

class SubscriberMailChannelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SubscribableServiceProvider::class,
        ];
    }

    /** @test */
    public function it_sends_mail_notifications_with_unsubscribe_links_for_mailing_lists()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotificationWithMailingList();
        $notifiable = new DummyNotifiableWithSubscriptions();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLink', $event->data);
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);

            $this->assertEquals(
                'https://testing.local/unsubscribe/testing-list',
                $event->data['unsubscribeLink']
            );
            $this->assertEquals(
                'https://testing.local/unsubscribe',
                $event->data['unsubscribeLinkForAll']
            );

            $this->assertTrue(
                $event->message->getHeaders()->has('List-Unsubscribe')
            );
            $this->assertEquals(
                '<https://testing.local/unsubscribe/testing-list>',
                $event->message->getHeaders()->get('List-Unsubscribe')->getFieldBody()
            );

            $this->assertStringContainsString(
                'If you no longer want to receive this type of email in the future use this',
                $event->message->getBody()
            );

            $this->assertStringContainsString(
                'To no longer receive any future emails',
                $event->message->getBody()
            );

            $this->assertStringContainsString(
                'https://testing.local/unsubscribe/testing-list',
                $event->message->getBody()
            );

            $this->assertStringContainsString(
                'https://testing.local/unsubscribe',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_sends_mail_notifications_with_mailing_links_via_queues()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotificationWithQueuing();
        $notifiable = new DummyNotifiableWithSubscriptions();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);

            $this->assertEquals(
                'https://testing.local/unsubscribe',
                $event->data['unsubscribeLinkForAll']
            );

            $this->assertTrue(
                $event->message->getHeaders()->has('List-Unsubscribe')
            );
            $this->assertEquals(
                '<https://testing.local/unsubscribe>',
                $event->message->getHeaders()->get('List-Unsubscribe')->getFieldBody()
            );

            $this->assertStringContainsString(
                'To no longer receive any future emails',
                $event->message->getBody()
            );

            $this->assertStringContainsString(
                'https://testing.local/unsubscribe',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_sends_mail_notifications_with_an_unsubscribe_link_for_all_emails()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotification();
        $notifiable = new DummyNotifiableWithSubscriptions();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);

            $this->assertEquals(
                'https://testing.local/unsubscribe',
                $event->data['unsubscribeLinkForAll']
            );

            $this->assertTrue(
                $event->message->getHeaders()->has('List-Unsubscribe')
            );
            $this->assertEquals(
                '<https://testing.local/unsubscribe>',
                $event->message->getHeaders()->get('List-Unsubscribe')->getFieldBody()
            );

            $this->assertStringContainsString(
                'To no longer receive any future emails',
                $event->message->getBody()
            );

            $this->assertStringContainsString(
                'https://testing.local/unsubscribe',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_sends_mail_notifications_normally_otherwise()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotification();
        $notifiable = new DummyNotifiable();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayNotHasKey('unsubscribeLinkForAll', $event->data);

            $this->assertFalse(
                $event->message->getHeaders()->has('List-Unsubscribe')
            );

            $this->assertStringNotContainsString(
                'To no longer receive any future emails',
                $event->message->getBody()
            );

            $this->assertStringNotContainsString(
                'https://testing.local/unsubscribe',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_sends_mail_notifications_normally_if_transactional()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyTransactionalNotification();
        $notifiable = new DummyNotifiableWithSubscriptions();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayNotHasKey('unsubscribeLinkForAll', $event->data);

            $this->assertFalse(
                $event->message->getHeaders()->has('List-Unsubscribe')
            );

            $this->assertStringNotContainsString(
                'To no longer receive any future emails',
                $event->message->getBody()
            );

            $this->assertStringNotContainsString(
                'https://testing.local/unsubscribe',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_handles_mailables_as_per_inherited_behavior()
    {
        View::addNamespace('testing', __DIR__.'/../views');

        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotification();
        $expectedNotification->useMailable = true;
        $notifiable = new DummyNotifiable();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertStringContainsString(
                'This is a dummy',
                $event->message->getBody()
            );

            return true;
        });
    }

    /** @test */
    public function it_checks_if_a_notifiable_is_subscribed_to_receive_the_notification()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $notification = new DummyNotificationWithMailingList();
        $notifiable = new DummyNotifiableWithSubscriptions();

        $notification->shouldCheck = true;
        $notifiable->isSubscribed = false;

        $notifiable->notify($notification);

        Event::assertNotDispatched(MessageSending::class);
    }

    /** @test */
    public function it_does_not_send_mail_if_there_is_no_email_to_route_to()
    {
        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $notification = new DummyNotification();
        $notifiable = new DummyNotifiable();
        $notifiable->email = null;

        $notifiable->notify($notification);

        Event::assertNotDispatched(MessageSending::class);
    }

    /** @test */
    public function it_uses_views_set_on_the_mail_message_from_the_notification()
    {
        View::addNamespace('testing', __DIR__.'/../views');

        Event::fake([
            MessageSending::class,
            MessageSent::class,
        ]);

        $expectedNotification = new DummyNotification();
        $expectedNotification->useView = 'testing::example';
        $notifiable = new DummyNotifiable();

        $notifiable->notify($expectedNotification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertStringContainsString(
                'This is a dummy',
                $event->message->getBody()
            );

            return true;
        });
    }
}
