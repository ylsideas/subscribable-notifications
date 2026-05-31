<?php

namespace YlsIdeas\SubscribableNotifications;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;

final class SubscribableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'subscriber');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/subscriber'),
            ], 'subscriber-views');

            $this->publishes([
                __DIR__.'/../stubs/SubscribableServiceProvider.stub' => app_path('Providers/SubscribableServiceProvider.php'),
            ], 'subscriber-provider');

            $this->publishes([
                __DIR__.'/../stubs/tests/UnsubscribeRouteTest.stub' => base_path('tests/Feature/UnsubscribeRouteTest.php'),
                __DIR__.'/../stubs/tests/SubscribableNotificationTest.stub' => base_path('tests/Feature/SubscribableNotificationTest.php'),
            ], 'subscriber-tests');
        }

        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->channel !== 'mail') {
                return;
            }
            if ($event->notifiable instanceof CheckSubscriptionStatusBeforeSendingNotifications &&
                $event->notification instanceof CheckNotifiableSubscriptionStatus &&
                $event->notification->checkMailSubscriptionStatus() &&
                ! $event->notifiable->mailSubscriptionStatus($event->notification)) {
                return false;
            }
        });
    }

    public function register(): void
    {
        $this->app->singleton(Subscriber::class, fn () => new Subscriber($this->app));
    }
}
