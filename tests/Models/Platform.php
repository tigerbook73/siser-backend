<?php
/**
 * Platform
 */
namespace Tests\Models;

/**
 * Platform
 */
class Platform
{
    /**
     * Possible values of this enum
     */
    const WINDOWS = 'Windows';

    const MAC = 'Mac';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::WINDOWS,
            self::MAC
        ];
    }
}
