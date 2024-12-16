<?php
/**
 * ProductItemCategory
 */
namespace Tests\Models;

/**
 * ProductItemCategory
 */
class ProductItemCategory
{
    /**
     * Possible values of this enum
     */
    const PLAN = 'plan';

    const LICENSE = 'license';

    const LICENSE_PRORATED = 'license-prorated';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PLAN,
            self::LICENSE,
            self::LICENSE_PRORATED
        ];
    }
}
