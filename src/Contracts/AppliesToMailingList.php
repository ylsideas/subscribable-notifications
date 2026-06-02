<?php

namespace YlsIdeas\SubscribableNotifications\Contracts;

interface AppliesToMailingList
{
    public function usesMailingList(): string|\BackedEnum;
}
