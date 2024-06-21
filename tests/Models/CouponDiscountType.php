<?php
/**
 * CouponDiscountType
 */
namespace Tests\Models;

/**
 * CouponDiscountType
 */
class CouponDiscountType
{
    /**
     * Possible values of this enum
     */
    const PERCENTAGE = 'percentage';

    const FREE_TRIAL = 'free-trial';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PERCENTAGE,
            self::FREE_TRIAL
        ];
    }
}
