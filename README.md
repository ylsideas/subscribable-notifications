# Subscribable Notifications for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)
[![Build Status](https://img.shields.io/travis/ylsideas/subscribable-notifications/master.svg?style=flat-square)](https://travis-ci.org/ylsideas/subscribable-notifications)
[![Quality Score](https://img.shields.io/scrutinizer/g/ylsideas/subscribable-notifications.svg?style=flat-square)](https://scrutinizer-ci.com/g/ylsideas/subscribable-notifications)
[![Total Downloads](https://img.shields.io/packagist/dt/ylsideas/subscribable-notifications.svg?style=flat-square)](https://packagist.org/packages/ylsideas/subscribable-notifications)
[![StyleCI](https://github.styleci.io/repos/192806647/shield?branch=master)](https://github.styleci.io/repos/192806647)

This package is designed to help you handle email unsubscribes with as little as 5 minutes setup. After installing
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
     \App\Providers\SubscriberServiceProvider::class,
     
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
class User implements CanUnsubscribe
{
    use Notifiable, MailSubscriber;
}
```

### Implementing your own `unsubscribeLink()` method

``` php
class User implements CanUnsubscribe
{
    use Notifiable;
    
    public function unsubscribeLink(?string $mailingList = null): string
    {
        return URL::signedRoute(
            'sorry-to-see-you-go,
            ['subscriber' => $this, 'mailingList' => $mailingList]
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
class Welcome extends Notification implements AppliesToMailingList
{
    public function usesMailingList(): string
    {
        return 'testing-list';
    }
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
            $user->mailing_lists[$mailingList] = false;
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
            return response()
                ->redirectTo('/unsubscribe-complete')
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