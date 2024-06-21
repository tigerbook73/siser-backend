<?php
/**
 * SubscriptionRenewalSubStatus
 */
namespace Tests\Models;

/**
 * SubscriptionRenewalSubStatus
 */
class SubscriptionRenewalSubStatus
{
    /**
     * Possible values of this enum
     */
    const NONE = 'none';

    const READY = 'ready';

    const FIRST_REMINDERED = 'first_remindered';

    const FINAL_REMINDERED = 'final_remindered';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NONE,
            self::READY,
            self::FIRST_REMINDERED,
            self::FINAL_REMINDERED
        ];
    }
}
