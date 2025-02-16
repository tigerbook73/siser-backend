<?php
/**
 * CouponInterval
 */
namespace Tests\Models;

/**
 * CouponInterval
 * @description Interval of the coupon. For free-trial coupon, interval must not be longterm; for percentage coupon, interval must be same as the plan's interval or longterm.
 */
class CouponInterval
{
    /**
     * Possible values of this enum
     */
    const DAY = 'day';

    const MONTH = 'month';

    const YEAR = 'year';

    const LONGTERM = 'longterm';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::DAY,
            self::MONTH,
            self::YEAR,
            self::LONGTERM
        ];
    }
}
