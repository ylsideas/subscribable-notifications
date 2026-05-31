<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Messages;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Mime\Email;
use YlsIdeas\SubscribableNotifications\Concerns\SubscribableNotification;
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;
use YlsIdeas\SubscribableNotifications\SubscribableServiceProvider;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiable;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiableWithSubscriptions;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotification;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithEnumMailingList;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithMailingList;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotificationWithQueuing;

class SubscribableMailMessageTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SubscribableServiceProvider::class,
        ];
    }

    public function test_via_defaults_to_the_package_markdown_template()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotification());

        $this->assertEquals('subscriber::html', $message->markdown);
    }

    public function test_via_sets_unsubscribe_link_for_all_emails()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotification());

        $this->assertArrayHasKey('unsubscribeLinkForAll', $message->viewData);
        $this->assertEquals('https://testing.local/unsubscribe', $message->viewData['unsubscribeLinkForAll']);
        $this->assertArrayNotHasKey('unsubscribeLink', $message->viewData);
    }

    public function test_via_sets_mailing_list_specific_unsubscribe_link()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotificationWithMailingList());

        $this->assertArrayHasKey('unsubscribeLink', $message->viewData);
        $this->assertEquals('https://testing.local/unsubscribe/testing-list', $message->viewData['unsubscribeLink']);
        $this->assertArrayHasKey('unsubscribeLinkForAll', $message->viewData);
    }

    public function test_via_resolves_enum_mailing_list_to_its_string_value()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotificationWithEnumMailingList());

        $this->assertEquals('https://testing.local/unsubscribe/newsletter', $message->viewData['unsubscribeLink']);
    }

    public function test_via_accepts_a_custom_template()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotification(), 'my-package::custom');

        $this->assertEquals('my-package::custom', $message->markdown);
    }

    public function test_trait_can_be_applied_to_custom_mail_message_class()
    {
        $customClass = new class () extends MailMessage {
            use SubscribableNotification;
        };

        $message = $customClass::via(new DummyNotifiableWithSubscriptions(), new DummyNotification());

        $this->assertInstanceOf($customClass::class, $message);
        $this->assertEquals('subscriber::html', $message->markdown);
        $this->assertArrayHasKey('unsubscribeLinkForAll', $message->viewData);
    }

    public function test_via_registers_a_symfony_message_callback()
    {
        $notifiable = new DummyNotifiableWithSubscriptions();
        $message = SubscribableMailMessage::via($notifiable, new DummyNotification());

        $this->assertCount(1, $message->callbacks);
    }

    public function test_it_sends_with_rfc8058_headers_for_mailing_list_notifications()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        (new DummyNotifiableWithSubscriptions())->notify(new DummyNotificationWithMailingList());

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLink', $event->data);
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);
            $this->assertEquals('https://testing.local/unsubscribe/testing-list', $event->data['unsubscribeLink']);
            $this->assertEquals('https://testing.local/unsubscribe', $event->data['unsubscribeLinkForAll']);
            $this->assertTrue($event->message->getHeaders()->has('List-Unsubscribe'));
            $this->assertEquals(
                '<https://testing.local/unsubscribe/testing-list>',
                $this->headerBody($event->message, 'List-Unsubscribe')
            );
            $this->assertTrue($event->message->getHeaders()->has('List-Unsubscribe-Post'));
            $this->assertEquals('List-Unsubscribe=One-Click', $this->headerBody($event->message, 'List-Unsubscribe-Post'));
            $content = $event->message->getHtmlBody();
            $this->assertStringContainsString('If you no longer want to receive this type of email in the future use this', $content);
            $this->assertStringContainsString('To no longer receive any future emails', $content);
            $this->assertStringContainsString('https://testing.local/unsubscribe/testing-list', $content);
            $this->assertStringContainsString('https://testing.local/unsubscribe', $content);

            return true;
        });
    }

    public function test_it_sends_with_rfc8058_headers_for_queued_notifications()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        (new DummyNotifiableWithSubscriptions())->notify(new DummyNotificationWithQueuing());

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);
            $this->assertEquals('https://testing.local/unsubscribe', $event->data['unsubscribeLinkForAll']);
            $this->assertTrue($event->message->getHeaders()->has('List-Unsubscribe'));
            $this->assertEquals('<https://testing.local/unsubscribe>', $this->headerBody($event->message, 'List-Unsubscribe'));
            $this->assertEquals('List-Unsubscribe=One-Click', $this->headerBody($event->message, 'List-Unsubscribe-Post'));
            $this->assertStringContainsString('To no longer receive any future emails', $event->message->getHtmlBody());

            return true;
        });
    }

    public function test_it_sends_with_unsubscribe_for_all_link_when_no_mailing_list()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        (new DummyNotifiableWithSubscriptions())->notify(new DummyNotification());

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLinkForAll', $event->data);
            $this->assertEquals('https://testing.local/unsubscribe', $event->data['unsubscribeLinkForAll']);
            $this->assertEquals('<https://testing.local/unsubscribe>', $this->headerBody($event->message, 'List-Unsubscribe'));
            $this->assertStringContainsString('To no longer receive any future emails', $event->message->getHtmlBody());

            return true;
        });
    }

    public function test_it_sends_without_unsubscribe_links_for_non_subscribable_notifiables()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        (new DummyNotifiable())->notify(new DummyNotification());

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayNotHasKey('unsubscribeLinkForAll', $event->data);
            $this->assertFalse($event->message->getHeaders()->has('List-Unsubscribe'));
            $this->assertStringNotContainsString('To no longer receive any future emails', $event->message->getHtmlBody() ?? '');

            return true;
        });
    }

    public function test_it_sends_with_rfc8058_headers_for_enum_mailing_list()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        (new DummyNotifiableWithSubscriptions())->notify(new DummyNotificationWithEnumMailingList());

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertArrayHasKey('unsubscribeLink', $event->data);
            $this->assertEquals('https://testing.local/unsubscribe/newsletter', $event->data['unsubscribeLink']);
            $this->assertEquals(
                '<https://testing.local/unsubscribe/newsletter>',
                $this->headerBody($event->message, 'List-Unsubscribe')
            );

            return true;
        });
    }

    public function test_it_cancels_send_when_notifiable_is_not_subscribed()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        $notification = new DummyNotificationWithMailingList();
        $notification->shouldCheck = true;
        $notifiable = new DummyNotifiableWithSubscriptions();
        $notifiable->isSubscribed = false;

        $notifiable->notify($notification);

        Event::assertNotDispatched(MessageSending::class);
    }

    public function test_it_does_not_send_when_there_is_no_email_address()
    {
        Event::fake([MessageSending::class, MessageSent::class]);

        $notifiable = new DummyNotifiable();
        $notifiable->email = null;

        $notifiable->notify(new DummyNotification());

        Event::assertNotDispatched(MessageSending::class);
    }

    public function test_it_handles_mailables()
    {
        View::addNamespace('testing', __DIR__.'/../views');
        Event::fake([MessageSending::class, MessageSent::class]);

        $notification = new DummyNotification();
        $notification->useMailable = true;

        (new DummyNotifiable())->notify($notification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertStringContainsString('This is a dummy', $event->message->getHtmlBody() ?? '');

            return true;
        });
    }

    public function test_it_uses_a_custom_view_when_set_on_the_message()
    {
        View::addNamespace('testing', __DIR__.'/../views');
        Event::fake([MessageSending::class, MessageSent::class]);

        $notification = new DummyNotification();
        $notification->useView = 'testing::example';

        (new DummyNotifiable())->notify($notification);

        Event::assertDispatched(MessageSending::class, function (MessageSending $event) {
            $this->assertStringContainsString('This is a dummy', $event->message->getHtmlBody() ?? '');

            return true;
        });
    }

    protected function headerBody(Email $message, string $header): string
    {
        return $message->getHeaders()->getHeaderBody($header);
    }
}
