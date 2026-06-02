<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

interface SubscriberContract
{
    public function routes(mixed $router = null, string|false $throttle = '60,1'): void;

    public function legacyRoutes(string $defaultModel, mixed $router = null, string|false $throttle = '60,1'): void;

    public function getLegacySubscriberType(): ?string;

    public function routeName(): string;

    public function onUnsubscribeFromMailingList(string|callable $handler): void;

    public function onUnsubscribeFromAllMailingLists(string|callable $handler): void;

    public function onCompletion(string|callable $handler): void;

    public function onCheckSubscriptionStatusOfAllMailingLists(string|callable $handler): void;

    public function onCheckSubscriptionStatusOfMailingList(string|callable $handler): void;

    public function unsubscribeFromMailingList(mixed $user, string $mailingList): void;

    public function unsubscribeFromAllMailingLists(mixed $user): void;

    public function complete(mixed $user, ?string $mailingList = null): mixed;

    public function checkSubscriptionStatus(mixed $user, ?string $mailingList = null): bool;
}
