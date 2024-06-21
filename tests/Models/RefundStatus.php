<?php
/**
 * RefundStatus
 */
namespace Tests\Models;

/**
 * RefundStatus
 */
class RefundStatus
{
    /**
     * Possible values of this enum
     */
    const PENDING = 'pending';

    const COMPLETED = 'completed';

    const FAILED = 'failed';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PENDING,
            self::COMPLETED,
            self::FAILED
        ];
    }
}
