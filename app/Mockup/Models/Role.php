<?php
/**
 * Role
 */
namespace App\Mockup\Models;

/**
 * Role
 */
class Role
{
    /**
     * Possible values of this enum
     */
    const ADMIN = 'admin';

    const SISER_BACKEND = 'siser-backend';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ADMIN,
            self::SISER_BACKEND
        ];
    }
}
