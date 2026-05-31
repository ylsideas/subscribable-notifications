<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

use Illuminate\Notifications\Notification;

/**
 * @deprecated v1.x will be removed in v2.0. Subscription gating is now handled by
 *             SubscribableMailMessage::via() rather than the SubscriberMailChannel.
 *             Remove this interface from your models.
 *             See the upgrade guide: UPGRADE.md
 */
interface CheckSubscriptionStatusBeforeSendingNotifications
{
    public function mailSubscriptionStatus(Notification $notification): bool;
}
