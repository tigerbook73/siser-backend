<?php
/**
 * InvoiceTypeCreate
 */
namespace Tests\Models;

/**
 * InvoiceTypeCreate
 */
class InvoiceTypeCreate
{
    /**
     * Possible values of this enum
     */
    const NEW_LICENSE_PACKAGE = 'new-license-package';

    const INCREASE_LICENSE_NUMBER = 'increase-license-number';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NEW_LICENSE_PACKAGE,
            self::INCREASE_LICENSE_NUMBER
        ];
    }
}
