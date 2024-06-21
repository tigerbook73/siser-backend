<?php
/**
 * SubscriptionRenewalStatus
 */
namespace Tests\Models;

/**
 * SubscriptionRenewalStatus
 */
class SubscriptionRenewalStatus
{
    /**
     * Possible values of this enum
     */
    const PENDING = 'pending';

    const ACTIVE = 'active';

    const COMPLETED = 'completed';

    const CANCELLED = 'cancelled';

    const EXPIRED = 'expired';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PENDING,
            self::ACTIVE,
            self::COMPLETED,
            self::CANCELLED,
            self::EXPIRED
        ];
    }
}
