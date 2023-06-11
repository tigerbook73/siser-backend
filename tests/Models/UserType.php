<?php
/**
 * UserType
 */
namespace Tests\Models;

/**
 * UserType
 */
class UserType
{
    /**
     * Possible values of this enum
     */
    const NORMAL = 'normal';

    const STAFF = 'staff';

    const VIP = 'vip';

    const BLACKLISTED = 'blacklisted';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NORMAL,
            self::STAFF,
            self::VIP,
            self::BLACKLISTED
        ];
    }
}
