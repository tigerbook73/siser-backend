<?php
/**
 * InvoiceSubStatus
 */
namespace Tests\Models;

/**
 * InvoiceSubStatus
 */
class InvoiceSubStatus
{
    /**
     * Possible values of this enum
     */
    const NONE = 'none';

    const TO_REFUND = 'to-refund';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NONE,
            self::TO_REFUND
        ];
    }
}
