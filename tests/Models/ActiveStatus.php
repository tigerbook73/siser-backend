<?php
/**
 * ActiveStatus
 */
namespace Tests\Models;

/**
 * ActiveStatus
 */
class ActiveStatus
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
