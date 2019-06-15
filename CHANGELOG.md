# Changelog

All notable changes to `unsubscribable-notification` will be documented in this file

## 0.1.0 - 2019-06-21

- Beta release
- Provides an extended MailChannel for notifications to handle subscriptions.
- Injects unsubscribe links into mail notifications based on the notifiable
implementing the `CanUnsubscribe` contract. Also has a `MailSubscriber` trait to
that implements the contract to work with a signed route for unsubscribing.
- Can add universal unsubscribe links as well as ones for a particular mailing list based
on the notification implementing the `AppliesToMailingList` contract.
- Has a controller to handle signed URLs for unsubscribing automatically. Can be disabled if
not required.
- Unsubscribing from a mailing list or all can be configured via the service provider.
- Provides events fired before and after unsubscribing has happened.
