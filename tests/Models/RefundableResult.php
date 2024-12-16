<?php
/**
 * RefundableResult
 */
namespace Tests\Models;

/**
 * RefundableResult
 */
class RefundableResult
{
    /**
     * Possible values of this enum
     */
    const REFUNDABLE = 'refundable';

    const NOT_REFUNDABLE = 'not_refundable';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::REFUNDABLE,
            self::NOT_REFUNDABLE
        ];
    }
}
