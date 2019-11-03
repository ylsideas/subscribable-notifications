# Changelog

All notable changes to `unsubscribable-notification` will be documented in this file

## 1.1.0 - 2019-11-03

### Altered

- Changes the view namespace to `subscriber` from `unsubscribe`. This was done to avoid confusion. This
would have potentially caused problems if you wanted to customise how the unsubscribe links worked visually.
If you update to this version and have previously published the vendor folder and then renamed the folder to
`unsubscribe` you will need to rename it to `subscriber`.

## 1.0.2 - 2019-09-05

### Added

- Updated for Laravel 6
- Updated TestBench to work with Laravel 6
- Updated PHPUnit to version 8 to work with TestBench

## 1.0.0 - 2019-06-27

### Added

- MailChannel to block notifications for emails if the user has unsubscribed.
- Can generate signed URLs for a user unsubscribing from a mailing list or all mail notifications.
- Handles the unsubscribing process.
