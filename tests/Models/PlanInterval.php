<?php
/**
 * PlanInterval
 */
namespace Tests\Models;

/**
 * PlanInterval
 */
class PlanInterval
{
    /**
     * Possible values of this enum
     */
    const DAY = 'day';

    const MONTH = 'month';

    const YEAR = 'year';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::DAY,
            self::MONTH,
            self::YEAR
        ];
    }
}
