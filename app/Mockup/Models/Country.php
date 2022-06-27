<?php
/**
 * Country
 */
namespace App\Mockup\Models;

/**
 * Country
 */
class Country
{
    /**
     * Possible values of this enum
     */
    const AUSTRALIA = 'Australia';

    const USA = 'USA';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::AUSTRALIA,
            self::USA
        ];
    }
}
