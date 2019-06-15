<?php

namespace YlsIdeas\SubscribableNotifications\Tests\Support;

use Illuminate\Mail\Mailable;

class DummyMailable extends Mailable
{
    public function build() {

        $this->to('test@testing.local');

        return $this->view('testing::example');
    }
}