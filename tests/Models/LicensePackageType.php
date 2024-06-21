<?php
/**
 * LicensePackageType
 */
namespace Tests\Models;

/**
 * LicensePackageType
 */
class LicensePackageType
{
    /**
     * Possible values of this enum
     */
    const STANDARD = 'standard';

    const EDUCATION = 'education';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::STANDARD,
            self::EDUCATION
        ];
    }
}
