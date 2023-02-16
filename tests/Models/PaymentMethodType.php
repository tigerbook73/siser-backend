<?php
/**
 * PaymentMethodType
 */
namespace Tests\Models;

/**
 * PaymentMethodType
 */
class PaymentMethodType
{
    /**
     * Possible values of this enum
     */
    const CREDIT_CARD = 'credit-card';

    const OTHER = 'other';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::CREDIT_CARD,
            self::OTHER
        ];
    }
}
