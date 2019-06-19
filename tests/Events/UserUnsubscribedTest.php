<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\SubscribableNotifications\Events\UserUnsubscribed;
use YlsIdeas\SubscribableNotifications\Tests\Support\DummyNotifiable;

class UserUnsubscribedTest extends TestCase
{
    /** @test */
    public function it_can_be_initialised_with_parameters()
    {
        $user = new DummyNotifiable();

        $event = new UserUnsubscribed($user, 'test');

        $this->assertEquals($user, $event->user);
        $this->assertEquals('test', $event->mailingList);
    }
}
