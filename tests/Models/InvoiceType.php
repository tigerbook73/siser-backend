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

    const NEW_LICENSE_PACKAGE = 'new-license-package';

    const INCREASE_LICENSE_NUMBER = 'increase-license-number';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NEW_SUBSCRIPTION,
            self::RENEW_SUBSCRIPTION,
            self::NEW_LICENSE_PACKAGE,
            self::INCREASE_LICENSE_NUMBER
        ];
    }
}
