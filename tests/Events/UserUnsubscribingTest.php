<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribing;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiable;

class UserUnsubscribingTest extends TestCase
{
    /** @test */
    public function it_can_be_initialised_with_parameters()
    {
        $user = new DummyNotifiable();

        $event = new UserUnsubscribing($user, 'test');

        $this->assertEquals($user, $event->user);
        $this->assertEquals('test', $event->mailingList);
    }
}
