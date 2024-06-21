<?php
/**
 * InvoiceStatus
 */
namespace Tests\Models;

/**
 * InvoiceStatus
 */
class InvoiceStatus
{
    /**
     * Possible values of this enum
     */
    const INIT = 'init';

    const OPEN = 'open';

    const PENDING = 'pending';

    const CANCELLED = 'cancelled';

    const PROCESSING = 'processing';

    const FAILED = 'failed';

    const COMPLETED = 'completed';

    const REFUNDING = 'refunding';

    const REFUND_FAILED = 'refund-failed';

    const REFUNDED = 'refunded';

    const PARTLY_REFUNDED = 'partly-refunded';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::INIT,
            self::OPEN,
            self::PENDING,
            self::CANCELLED,
            self::PROCESSING,
            self::FAILED,
            self::COMPLETED,
            self::REFUNDING,
            self::REFUND_FAILED,
            self::REFUNDED,
            self::PARTLY_REFUNDED
        ];
    }
}
