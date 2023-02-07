<?php
/**
 * ActiveStatusAndAll
 */
namespace Tests\Models;

/**
 * ActiveStatusAndAll
 */
class ActiveStatusAndAll
{
    /**
     * Possible values of this enum
     */
    const ACTIVE = 'active';

    const INACTIVE = 'inactive';

    const ALL = 'all';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::ALL
        ];
    }
}
