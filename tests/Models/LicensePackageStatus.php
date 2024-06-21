<?php
/**
 * LicensePackageStatus
 */
namespace Tests\Models;

/**
 * LicensePackageStatus
 */
class LicensePackageStatus
{
    /**
     * Possible values of this enum
     */
    const ACTIVE = 'active';

    const INACTIVE = 'inactive';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ACTIVE,
            self::INACTIVE
        ];
    }
}
