<?php
/**
 * SoftwarePackageVersionType
 */
namespace Tests\Models;

/**
 * SoftwarePackageVersionType
 */
class SoftwarePackageVersionType
{
    /**
     * Possible values of this enum
     */
    const STABLE = 'stable';

    const BETA = 'beta';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::STABLE,
            self::BETA
        ];
    }
}
