<?php
/**
 * CouponType
 */
namespace Tests\Models;

/**
 * CouponType
 */
class CouponType
{
    /**
     * Possible values of this enum
     */
    const SHARED = 'shared';

    const SHARED_ONCE = 'shared-once';

    const ONCE_OFF = 'once-off';

    const ONCE_OFF_WITH_EVENT = 'once-off-with-event';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SHARED,
            self::SHARED_ONCE,
            self::ONCE_OFF,
            self::ONCE_OFF_WITH_EVENT
        ];
    }
}
