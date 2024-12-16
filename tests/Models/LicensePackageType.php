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
    const SINGLE = 'single';

    const STANDARD = 'standard';

    const EDUCATION = 'education';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SINGLE,
            self::STANDARD,
            self::EDUCATION
        ];
    }
}
