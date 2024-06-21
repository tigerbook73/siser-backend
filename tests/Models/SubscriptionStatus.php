<?php
/**
 * SubscriptionStatus
 */
namespace Tests\Models;

/**
 * SubscriptionStatus
 */
class SubscriptionStatus
{
    /**
     * Possible values of this enum
     */
    const ACTIVE = 'active';

    const DRAFT = 'draft';

    const FAILED = 'failed';

    const PENDING = 'pending';

    const STOPPED = 'stopped';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ACTIVE,
            self::DRAFT,
            self::FAILED,
            self::PENDING,
            self::STOPPED
        ];
    }
}
