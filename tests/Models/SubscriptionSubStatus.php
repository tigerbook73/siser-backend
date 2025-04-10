<?php
/**
 * SubscriptionSubStatus
 */
namespace Tests\Models;

/**
 * SubscriptionSubStatus
 */
class SubscriptionSubStatus
{
    /**
     * Possible values of this enum
     */
    const CANCELLING = 'cancelling';

    const NORMAL = 'normal';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::CANCELLING,
            self::NORMAL
        ];
    }
}
