# Upgrade Guide

## v1 → v2

### Overview of breaking changes

| Area | v1 | v2 |
|---|---|---|
| PHP | 8.1+ | **8.4+** |
| Laravel | 9 / 10 / 11 | **12 / 13** |
| Service provider | Extend abstract `SubscribableApplicationServiceProvider` | Publish a plain stub and fill it in |
| Model binding | Single model class configured via `Subscriber::userModel()` | Polymorphic — any model works |
| Unsubscribe URL shape | `unsubscribe/{id}/{list?}` | `unsubscribe/{type}/{id}/{list?}` |
| Notification opt-in | Automatic for all `mail` channel notifications | Explicit — use `SubscribableMailMessage::via()` in `toMail()` |

---

### Step 1 — Update composer.json

```bash
composer require ylsideas/subscribable-notifications:^2.0
```

---

### Step 2 — Replace SubscribableApplicationServiceProvider

v1 required a class that extended the package's abstract `SubscribableApplicationServiceProvider` with five abstract methods. v2 replaces this with a plain stub you publish and configure.

**Remove** your existing implementation (typically `App\Providers\SubscribableServiceProvider`):

```php
// v1 — delete this
class SubscribableServiceProvider extends SubscribableApplicationServiceProvider
{
    protected $model = User::class;

    public function onUnsubscribeFromMailingList($user, $mailingList): void { ... }
    public function onUnsubscribeFromAllMailingLists($user): void { ... }
    public function onCompletion($user, ?string $mailingList): RedirectResponse { ... }
    public function onCheckSubscriptionStatusForMailingLists($user, $mailingList): bool { ... }
    public function onCheckSubscriptionStatusForAllMailingLists($user): bool { ... }
}
```

**Publish** the new stub:

```bash
php artisan vendor:publish --tag=subscriber-provider
```

This creates `App\Providers\SubscribableServiceProvider`. Fill in the five callbacks — the logic you had in the abstract methods moves directly into `boot()`:

```php
public function boot(): void
{
    Subscriber::routes();

    Subscriber::onUnsubscribeFromMailingList(function ($notifiable, string $mailingList): void {
        // e.g. $notifiable->subscriptions()->where('list', $mailingList)->delete();
    });

    Subscriber::onUnsubscribeFromAllMailingLists(function ($notifiable): void {
        // e.g. $notifiable->subscriptions()->delete();
    });

    Subscriber::onCompletion(function ($notifiable, ?string $mailingList) {
        return redirect('/');
    });

    Subscriber::onCheckSubscriptionStatusOfMailingList(function ($notifiable, string $mailingList): bool {
        return true; // e.g. $notifiable->isSubscribedTo($mailingList)
    });

    Subscriber::onCheckSubscriptionStatusOfAllMailingLists(function ($notifiable): bool {
        return true; // e.g. ! $notifiable->hasUnsubscribedFromAll()
    });
}
```

Register the provider in `bootstrap/providers.php` if it isn't there already.

---

### Step 3 — Update your subscriber model

Remove any `Subscriber::userModel(User::class)` calls — this method no longer exists. The model is now resolved polymorphically from the URL.

Your model must implement `CanUnsubscribe` and use the `MailSubscriber` trait. If it already does, no change is needed here; the trait now generates polymorphic URLs automatically.

```php
use YlsIdeas\SubscribableNotifications\Contracts\CanUnsubscribe;
use YlsIdeas\SubscribableNotifications\MailSubscriber;

class User extends Authenticatable implements CanUnsubscribe
{
    use MailSubscriber;
}
```

**Add a morph map (strongly recommended).** Without one, the full class name (`App\Models\User`) appears in every unsubscribe URL. A morph map gives you a short, stable key instead:

```php
// AppServiceProvider::boot()
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'user' => \App\Models\User::class,
]);
```

With this in place, URLs contain `user` instead of the full class name, and refactoring or renaming your model won't invalidate existing links.

---

### Step 4 — Update your notifications

v1 automatically injected unsubscribe links into every `mail` channel notification by replacing the built-in `MailChannel`. v2 requires explicit opt-in — return `SubscribableMailMessage::via($notifiable, $this)` instead of a plain `MailMessage`.

```php
// v1
use Illuminate\Notifications\Messages\MailMessage;

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage())
        ->subject('Your weekly digest')
        ->line('Here is your digest...');
}
```

```php
// v2
use YlsIdeas\SubscribableNotifications\Messages\SubscribableMailMessage;

public function toMail(object $notifiable): SubscribableMailMessage
{
    return SubscribableMailMessage::via($notifiable, $this)
        ->subject('Your weekly digest')
        ->line('Here is your digest...');
}
```

`via()` sets the unsubscribe view data and registers the RFC 8058 `List-Unsubscribe` and `List-Unsubscribe-Post` headers via a `withSymfonyMessage()` callback. It also defaults the markdown template to `subscriber::html`; pass a third argument to override:

```php
SubscribableMailMessage::via($notifiable, $this, 'my-theme::email')
```

If you want to apply the same behaviour to a custom `MailMessage` subclass, use the `SubscribableNotification` trait directly instead of extending `SubscribableMailMessage`:

```php
use Illuminate\Notifications\Messages\MailMessage;
use YlsIdeas\SubscribableNotifications\Concerns\SubscribableNotification;

class MyMailMessage extends MailMessage
{
    use SubscribableNotification;
}
```

---

### Step 5 — Handle the route URL change (critical for live systems)

This is the most important production concern. Every unsubscribe link already delivered to a user's inbox points at the v1 URL shape:

```
# v1
https://example.com/unsubscribe/123/newsletter?signature=...

# v2
https://example.com/unsubscribe/user/123/newsletter?signature=...
```

Those v1 links will stop working the moment you deploy v2 if nothing is done.

#### Option A — Register the legacy compatibility route (recommended)

Call `Subscriber::legacyRoutes()` alongside `Subscriber::routes()` in your service provider:

```php
public function boot(): void
{
    Subscriber::routes();
    Subscriber::legacyRoutes(App\Models\User::class); // default model for old links

    // ... handlers
}
```

This registers a second route at the old URL shape (`unsubscribe/{id}/{list?}`) that forwards requests to the same controller, automatically resolving the model from the class you provide. The v1 signed URL signatures are over the URL path, which is unchanged, so they validate correctly.

Keep `legacyRoutes()` active for as long as you consider old emails to still be in circulation. A safe window is 6–12 months, but you can remove it earlier once you are confident v1-style links have expired.

#### Option B — Accept that old links break

If your use case is transactional (receipts, one-time alerts) rather than recurring newsletters, old links may not matter. Remove the legacy route call and move on.

---

### Step 6 — Optional: migrate mailing list identifiers to enums

v1 mailing lists were plain strings. v2 supports PHP 8.1 backed enums via the `AppliesToMailingList` contract:

```php
// Before
public function usesMailingList(): string
{
    return 'newsletter';
}

// After
enum MailingList: string
{
    case Newsletter = 'newsletter';
}

public function usesMailingList(): string|\BackedEnum
{
    return MailingList::Newsletter;
}
```

The enum's raw value (`'newsletter'`) is used in the URL, so existing links continue to work.

---

### Step 7 — Optional: use Subscriber::fake() in tests

v2 adds a test fake so you can assert unsubscribe behaviour without side effects:

```php
$fake = Subscriber::fake();

// ... trigger unsubscribe ...

$fake->assertUnsubscribedFromMailingList($user, 'newsletter');
$fake->assertUnsubscribedFromAll($user);
$fake->assertNothingUnsubscribed();
```

Control subscription status in tests:

```php
$fake->alwaysSubscribed();    // all subscription checks return true
$fake->alwaysUnsubscribed();  // all subscription checks return false
```
