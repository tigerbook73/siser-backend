<?php
/**
 * InvoiceType
 */
namespace Tests\Models;

/**
 * InvoiceType
 */
class InvoiceType
{
    /**
     * Possible values of this enum
     */
    const NEW_SUBSCRIPTION = 'new-subscription';

    const RENEW_SUBSCRIPTION = 'renew-subscription';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NEW_SUBSCRIPTION,
            self::RENEW_SUBSCRIPTION
        ];
    }
}
