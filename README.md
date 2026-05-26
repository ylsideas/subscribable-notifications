# Subscribable Notifications for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ylsideas/subscribable-notifications/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ylsideas/subscribable-notifications/actions/workflows/run-tests.yml)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ylsideas/subscribable-notifications/php-cs-fixer.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ylsideas/subscribable-notifications/actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/ylsideas/subscribable-notifications?style=flat)](https://packagist.org/packages/ylsideas/subscribable-notifications)

Handle email unsubscribes with minimal setup. The package injects unsubscribe links into notification emails, provides a signed unsubscribe route and controller, and fully complies with [RFC 8058](https://www.rfc-editor.org/rfc/rfc8058) one-click unsubscribe — required by Gmail and Yahoo for bulk senders since 2024.

Every email sent through the package will include both headers automatically:

```
List-Unsubscribe: <https://example.com/unsubscribe/...>
List-Unsubscribe-Post: List-Unsubscribe=One-Click
```

The unsubscribe route accepts `GET` (browser link) and `POST` (one-click from email clients), so users can unsubscribe without ever opening a browser.

## Requirements

- PHP 8.4+
- Laravel 12 or 13

## Installation

```bash
composer require ylsideas/subscribable-notifications:^2.0
```

Publish the application service provider:

```bash
php artisan vendor:publish --tag=subscriber-provider
```

This creates `App\Providers\SubscribableServiceProvider`. Register it in `bootstrap/providers.php` (Laravel 11+):

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SubscribableServiceProvider::class,
];
```

Or in `config/app.php` for older projects:

```php
'providers' => [
    // ...
    App\Providers\SubscribableServiceProvider::class,
],
```

The published provider is a plain `ServiceProvider` — open it and fill in the handler closures. There is no base class to extend or abstract methods to implement:

```php
use Illuminate\Support\ServiceProvider;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

class SubscribableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Subscriber::routes();

        Subscriber::onUnsubscribeFromMailingList(function ($notifiable, string $mailingList) {
            // Remove the notifiable's subscription to the given mailing list.
        });

        Subscriber::onUnsubscribeFromAllMailingLists(function ($notifiable) {
            // Remove the notifiable's subscription to all mailing lists.
        });

        Subscriber::onCompletion(function ($notifiable, ?string $mailingList) {
            return redirect('/');
        });

        Subscriber::onCheckSubscriptionStatusOfMailingList(function ($notifiable, string $mailingList): bool {
            return true;
        });

        Subscriber::onCheckSubscriptionStatusOfAllMailingLists(function ($notifiable): bool {
            return true;
        });
    }
}
```

## Setup

### 1. Apply the trait to your notifiable model

The `MailSubscriber` trait can be applied to **any Eloquent model** — not just `User`. The package uses Laravel's [morph map](https://laravel.com/docs/eloquent-relationships#custom-polymorphic-types) to identify models in signed URLs, so it works with multiple notifiable types side-by-side.

```php
use YlsIdeas\SubscribableNotifications\MailSubscriber;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;

class User extends Authenticatable implements CanUnsubscribe, CheckSubscriptionStatusBeforeSendingNotifications
{
    use Notifiable, MailSubscriber;
}
```

You can apply it to any model that receives notifications:

```php
class Contact extends Model implements CanUnsubscribe, CheckSubscriptionStatusBeforeSendingNotifications
{
    use Notifiable, MailSubscriber;
}
```

### 2. Register a morph map (recommended)

Without a morph map, the full class name appears in unsubscribe URLs. Registering aliases keeps URLs short and decouples them from your class names:

```php
// In AppServiceProvider::boot()
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'user'    => \App\Models\User::class,
    'contact' => \App\Models\Contact::class,
]);
```

With this in place, a `User` unsubscribe URL looks like:

```
https://example.com/unsubscribe/user/42?signature=...
```

Without it, the full class name is used instead.

## Usage

### Sending notifications with unsubscribe links

No changes are needed to your notifications. Once the trait is on the model, every email notification sent to that model will automatically include unsubscribe links in the footer and the RFC 8058 headers.

### Mailing list notifications

Implement `AppliesToMailingList` on a notification to include a second, list-specific unsubscribe link alongside the global one.

You can use a plain string:

```php
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;

class WeeklyDigest extends Notification implements AppliesToMailingList
{
    public function usesMailingList(): string
    {
        return 'weekly-digest';
    }
}
```

Or a backed enum (recommended for type safety):

```php
enum MailingList: string
{
    case WeeklyDigest = 'weekly-digest';
    case ProductUpdates = 'product-updates';
}

class WeeklyDigest extends Notification implements AppliesToMailingList
{
    public function usesMailingList(): string|\BackedEnum
    {
        return MailingList::WeeklyDigest;
    }
}
```

### Configuring the unsubscribe handlers

The five `Subscriber::on*` calls in the published provider are the only configuration needed. Each accepts a closure or a `Class@method` string that will be resolved from the service container:

```php
Subscriber::onUnsubscribeFromAllMailingLists(\App\Handlers\UnsubscribeHandler::class . '@handleAll');
```

A realistic implementation might look like:

```php
Subscriber::onUnsubscribeFromMailingList(function ($notifiable, string $mailingList) {
    $notifiable->subscriptions()->where('list', $mailingList)->delete();
});

Subscriber::onUnsubscribeFromAllMailingLists(function ($notifiable) {
    $notifiable->update(['unsubscribed_at' => now()]);
});

Subscriber::onCompletion(function ($notifiable, ?string $mailingList) {
    return redirect()->route('unsubscribe.confirmed');
});

Subscriber::onCheckSubscriptionStatusOfMailingList(function ($notifiable, string $mailingList): bool {
    return $notifiable->subscriptions()->where('list', $mailingList)->exists();
});

Subscriber::onCheckSubscriptionStatusOfAllMailingLists(function ($notifiable): bool {
    return $notifiable->unsubscribed_at === null;
});
```

### Blocking sends for unsubscribed users

To prevent notifications being sent to users who have opted out, implement `CheckNotifiableSubscriptionStatus` on the notification:

```php
use YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus;

class WeeklyDigest extends Notification implements AppliesToMailingList, CheckNotifiableSubscriptionStatus
{
    public function checkMailSubscriptionStatus(): bool
    {
        return true;
    }
}
```

When this returns `true`, the channel checks `$notifiable->mailSubscriptionStatus($notification)` before sending. The `MailSubscriber` trait implements this automatically using your configured handlers. If both the mailing-list check and the all-mail check return `true`, the email sends; otherwise it is silently dropped.

### Custom unsubscribe link

If you implement `CanUnsubscribe` directly instead of using the `MailSubscriber` trait, generate your own signed URL:

```php
use Illuminate\Support\Facades\URL;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

class User extends Authenticatable implements CanUnsubscribe
{
    use Notifiable;

    public function unsubscribeLink(?string $mailingList = null): string
    {
        return URL::signedRoute(
            Subscriber::routeName(),
            [
                'subscriberType' => $this->getMorphClass(),
                'subscriberId'   => $this->getRouteKey(),
                'mailingList'    => $mailingList,
            ]
        );
    }
}
```

### Customising the email templates

The default templates inject a small unsubscribe block into the footer of all notification emails. Publish them to customise:

```bash
php artisan vendor:publish --tag=subscriber-views
```

This creates `resources/views/vendor/subscriber/html.blade.php` and `text.blade.php`.

## Testing

Use `Subscriber::fake()` in your tests to swap in a fake implementation and make assertions without needing real handlers configured:

```php
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

it('unsubscribes the user from the newsletter', function () {
    $fake = Subscriber::fake();
    $user = User::factory()->create();

    $this->get($user->unsubscribeLink('newsletter'));

    $fake->assertUnsubscribedFromMailingList($user, 'newsletter');
});

it('unsubscribes the user from all emails', function () {
    $fake = Subscriber::fake();
    $user = User::factory()->create();

    $this->get($user->unsubscribeLink());

    $fake->assertUnsubscribedFromAll($user);
});
```

Available assertions:

| Method | Description |
|--------|-------------|
| `assertUnsubscribedFromMailingList($notifiable, $list)` | Assert the notifiable was unsubscribed from a specific list |
| `assertUnsubscribedFromAll($notifiable)` | Assert the notifiable was globally unsubscribed |
| `assertNothingUnsubscribed()` | Assert no unsubscribe actions occurred |

To control subscription status checks in feature tests:

```php
Subscriber::fake()->alwaysUnsubscribed(); // all checkSubscriptionStatus calls return false
Subscriber::fake()->alwaysSubscribed();   // all checkSubscriptionStatus calls return true (default)
```

## Running the test suite

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email peter.fox@ylsideas.co instead of using the issue tracker.

## Credits

- [Peter Fox](https://github.com/peterfox)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
