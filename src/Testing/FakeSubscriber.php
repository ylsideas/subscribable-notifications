<?php

namespace YlsIdeas\SubscribableNotifications\Testing;

use Illuminate\Http\Response;
use PHPUnit\Framework\Assert;

class FakeSubscriber
{
    public string $routeName = 'unsubscribe';

    protected array $unsubscribedFromMailingList = [];

    protected array $unsubscribedFromAll = [];

    protected bool $subscriptionStatus = true;

    public function routes($router = null): void
    {
    }

    public function routeName(): string
    {
        return $this->routeName;
    }

    public function onUnsubscribeFromMailingList($handler): void
    {
    }

    public function onUnsubscribeFromAllMailingLists($handler): void
    {
    }

    public function onCompletion($handler): void
    {
    }

    public function onCheckSubscriptionStatusOfAllMailingLists($handler): void
    {
    }

    public function onCheckSubscriptionStatusOfMailingList($handler): void
    {
    }

    public function unsubscribeFromMailingList($user, string $mailingList): void
    {
        $this->unsubscribedFromMailingList[] = ['user' => $user, 'list' => $mailingList];
    }

    public function unsubscribeFromAllMailingLists($user): void
    {
        $this->unsubscribedFromAll[] = $user;
    }

    public function complete($user, ?string $mailingList = null): Response
    {
        return new Response('', 200);
    }

    public function checkSubscriptionStatus($user, ?string $mailingList = null): bool
    {
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
