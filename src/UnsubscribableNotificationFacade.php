<?php

namespace Ylsideas\UnsubscribableNotification;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ylsideas\UnsubscribableNotification\Skeleton\SkeletonClass
 */
class UnsubscribableNotificationFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'unsubscribable-notification';
    }
}
