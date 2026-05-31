<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

/**
 * @deprecated v1.x will be removed in v2.0. Subscription gating is now handled by
 *             SubscribableMailMessage::via() rather than the SubscriberMailChannel.
 *             Remove this interface from your notifications.
 *             See the upgrade guide: UPGRADE.md
 */
interface CheckNotifiableSubscriptionStatus
{
    public function checkMailSubscriptionStatus(): bool;
}
