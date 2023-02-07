<?php
/**
 * Role
 */
namespace Tests\Models;

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

    const SUPPORT = 'support';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ADMIN,
            self::SISER_BACKEND,
            self::SUPPORT
        ];
    }
}
