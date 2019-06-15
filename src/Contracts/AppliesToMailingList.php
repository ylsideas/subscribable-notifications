<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

interface AppliesToMailingList
{
    /**
     * @return string
     */
    public function usesMailingList(): string;
}
