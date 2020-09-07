# Subscribable Notifications for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ylsideas/subscribable-notifications/Tests?label=tests)](https://github.com/ylsideas/subscribable-notifications/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)

This package has been designed to help you handle email unsubscribes with as little as 5 minutes setup. After installing
your notifications sent over email should now be delivered with unsubscribe links in the footer and as a mail header
which email clients can present to the user for quicker unsubscribing. It can also handle resolving the unsubscribing 
of the user through a signed route/controller.

## Installation

You can install the package via composer:

```bash
composer require ylsideas/subscribable-notifications
```

Optionally to make use of the built in unsubscribing handler you can publish the application service
provider. If you wish to implement your own unsubscribing process and only insert unsubscribe links into
your notifications, you can forgo doing this.

```bash
php artisan vendor:publish --tag=subscriber-provider
```

This will create a `\App\Providers\SubscriberServiceProvider` class which you will need to register
in `config/app.php`.

```php
'providers' => [
    ...
    
    /*
     * Package Service Providers...
     */
     \App\Providers\SubscribableServiceProvider::class,
     
     ...
]
```

After this you can configure your unsubscribe handlers quickly as methods within the service provider that return the closures.

The package itself does not determine how you store or evaluate your users' subscribed state. Instead
it provides hooks in which to handle that.

## Usage

First off you must implement the `YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe` interface
on your notifiable User model. You can also apply the `YlsIdeas\SubscribableNotifications\MailSubscriber` trait
which will implement this for you to automatically provide signed urls for the unsubscribe controller provided
by this library.

``` php
use YlsIdeas\SubscribableNotifications\MailSubscriber;
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;

class User implements CanUnsubscribe
{
    use Notifiable, MailSubscriber;
}
```

### Implementing your own unsubscribe links

If you wish to implement your own completely different `unsubscribeLink()` method you can.

``` php
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;

class User implements CanUnsubscribe
{
    use Notifiable;
    
    public function unsubscribeLink(?string $mailingList = ''): string
    {
        return URL::signedRoute(
            'sorry-to-see-you-go',
            ['subscriber' => $this, 'mailingList' => $mailingList],
            now()->addDays(1)
        );
    }
}
```

### Implementing notifications as part of a mailing list

If you wish to apply specific mailing lists to notifications you need to implement the 
`YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList` on those notifications.
This will put two unsubscribe links into your emails generated from those notifications.
One for all emails and one for only that type of email.

``` php
use YlsIdeas\SubscribableNotifications\Contracts\AppliesToMailingList;

class Welcome extends Notification implements AppliesToMailingList
{
    ...
    
    public function usesMailingList(): string
    {
        return 'weekly-updates';
    }
    
    ...
}
```

### Using the full unsubscribing workflow

Using the `App\Providers\SubscriberServiceProvider` you can set up simple hooks to handle
unsubscribing the user from all future emails. This package doesn't determine how you should
store that record of opting out of future emails. Instead you provide functions in the provider
which will be called. The following are just examples of what you can do.

#### Implementing an unsubscribe hook for a specific mailing list

This handler will be called if a user links a link through to unsubscribe for a specific mailing list.

``` php
public class SubscriberServiceProvider
{
    ...
    
    public function onUnsubscribeFromMailingList()
    {
        return function ($user, $mailingList) {
            $user->mailing_lists = $user->mailing_lists->put($mailingList, false);
            $user->save();
        };
    }
    
    ...
}
```

#### Implementing an unsubscribe hook for all emails

This handler will be called if the user has clicked through to the link to unsubscribe from all future emails.

``` php
public class SubscriberServiceProvider
{
    ...
    
    public function onUnsubscribeFromAllMailingLists()
    {
        return function ($user) {
            $user->unsubscribed_at = now();
            $user->save();
        };
    }
    
    ...
}
```

#### Implementing an unsubscribe response

The completion handler will be called after a user is unsubscribed, allowing you to customise where the user is
redirected to or if you want to maybe show a further form even.

``` php
public class SubscriberServiceProvider
{
    ...
    
    public function onCompletion()
    {
        return function ($user, $mailingList) {
            return view('confirmation')
                ->with('alert', 'You\'re not unsubscribed');
        };
    }
    
    ...
}  
```

### Dedicated handler

You may also provide a string in the format of `class@method` that the subscriber class will use to grab the class
from the service container and then call the specified method on if you want to do something more custom. 

```php
public class SubscriberServiceProvider
{
    ...
    
    public function onUnsubscribeFromAllMailingLists()
    {
        return '\App\UnsubscribeHandler@handleUnsubscribing';
    }
    
    ...
}
```

### Checking if a notification should be sent per the subscription

You can also add hooks to check if a user should receive notifications for a mailing
list or for all mail notifications.

To do this you need to make sure your user has the 
`YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications` interface
implemented. The `YlsIdeas\SubscribableNotifications\MailSubscriber` trait will implement this for you to use the
built in Subscriber handlers.

If you want to implement a method yourself to check the subscription you could also just implement the method yourself
like in the example below.

``` php
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\Contracts\CheckSubscriptionStatusBeforeSendingNotifications;
use YlsIdeas\SubscribableNotifications\Facades\Subscriber;

class User implements CanUnsubscribe, CheckSubscriptionStatusBeforeSendingNotifications
{
    use Notifiable;
    
    
    public function mailSubscriptionStatus(Notification $notification) : bool
    {
        return Subscriber::checkSubscriptionStatus(
            $this,
            $notification instanceof AppliesToMailingList
                ? $notification->usesMailingList()
                : null
        );
    }
}
```

Then you need to implement the 
`YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus` interface on the notifications
that should trigger a check of the subscription status of the user it's being sent to. Then you just need to return
`true` if the subscription status should be checked.

``` php
use YlsIdeas\SubscribableNotifications\Contracts\CheckNotifiableSubscriptionStatus;

class Welcome extends Notification implements CheckNotifiableSubscriptionStatus
{
    ...
    
    public function checkMailSubscriptionStatus() : bool
    {
        return true;
    }
    
    ...
}
```

To use the functionality you then need to add your own Subscription check hooks. These hooks can be implemented
as you see fit.

``` php
public class SubscriberServiceProvider
{
    ...

    public function onCheckSubscriptionStatusOfMailingList()
    {
        return function ($user, $mailingList) {
            return $user->mailing_lists->get($mailingList, false);
        };
    }

    public function onCheckSubscriptionStatusOfAllMailingLists()
    {
        return function ($user) {
            return $user->unsubscribed_at === null;
        };
    }
    
    ...
}  
```

### Customising the email templates

Out of the box the emails generated use the same templates except that they
inject a small bit of text into the footer of the emails. If you wish you customise
the templates further you may publish the views.

```bash
php artisan vendor:publish --tag=subscriber-views
```

This will create a `resources/views/vendor/subscriber` folder containing both `html.blade.php`
and `text.blade.php` which can be customised. These will then be the defaults used by the
notification mail channel.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email peter.fox@ylsideas.co instead of using the issue tracker.

## Credits

- [Peter Fox](https://github.com/peterfox)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
