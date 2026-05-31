<?php

namespace YlsIdeas\SubscribableNotifications\Testing;

use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;

final class FakeSubscriber
{
    public string $routeName = 'unsubscribe';

    private array $unsubscribedFromMailingList = [];
    private array $unsubscribedFromAll = [];
    private array $subscriptionStatusChecks = [];
    private bool $subscriptionStatus = true;

    public function routes(mixed $router = null): void
    {
    }

    public function legacyRoutes(string $defaultModel, mixed $router = null): void
    {
    }

    public function routeName(): string
    {
        return $this->routeName;
    }

    public function onUnsubscribeFromMailingList(mixed $handler): void
    {
    }

    public function onUnsubscribeFromAllMailingLists(mixed $handler): void
    {
    }

    public function onCompletion(mixed $handler): void
    {
    }

    public function onCheckSubscriptionStatusOfAllMailingLists(mixed $handler): void
    {
    }

    public function onCheckSubscriptionStatusOfMailingList(mixed $handler): void
    {
    }

    public function unsubscribeFromMailingList(mixed $user, string $mailingList): void
    {
        $this->unsubscribedFromMailingList[] = ['user' => $user, 'list' => $mailingList];
    }

    public function unsubscribeFromAllMailingLists(mixed $user): void
    {
        $this->unsubscribedFromAll[] = $user;
    }

    public function complete(mixed $user, ?string $mailingList = null): Response
    {
        return new Response('', 200);
    }

    public function checkSubscriptionStatus(mixed $user, ?string $mailingList = null): bool
    {
        $this->subscriptionStatusChecks[] = ['user' => $user, 'mailingList' => $mailingList];

        return $this->subscriptionStatus;
    }

    public function alwaysSubscribed(): static
    {
        $this->subscriptionStatus = true;

        return $this;
    }

    public function alwaysUnsubscribed(): static
    {
        $this->subscriptionStatus = false;

        return $this;
    }

    public function assertUnsubscribedFromMailingList(mixed $user, string $mailingList): void
    {
        Assert::assertTrue(
            collect($this->unsubscribedFromMailingList)
                ->contains(fn ($item) => $item['user'] === $user && $item['list'] === $mailingList),
            "Failed asserting that the user was unsubscribed from [{$mailingList}]."
        );
    }

    public function assertUnsubscribedFromAll(mixed $user): void
    {
        Assert::assertTrue(
            collect($this->unsubscribedFromAll)->contains($user),
            'Failed asserting that the user was unsubscribed from all mailing lists.'
        );
    }

    public function assertCheckedSubscriptionStatus(mixed $user, ?string $mailingList): void
    {
        Assert::assertTrue(
            collect($this->subscriptionStatusChecks)
                ->contains(fn ($item) => $item['user'] === $user && $item['mailingList'] === $mailingList),
            $mailingList !== null
                ? "Failed asserting that subscription status was checked for mailing list [{$mailingList}]."
                : 'Failed asserting that subscription status was checked for all mailing lists.'
        );
    }

    public function assertNothingUnsubscribed(): void
    {
        Assert::assertEmpty(
            $this->unsubscribedFromMailingList,
            'Failed asserting that no mailing list unsubscribes occurred.'
        );
        Assert::assertEmpty(
            $this->unsubscribedFromAll,
            'Failed asserting that no all-mail unsubscribes occurred.'
        );
    }
}
