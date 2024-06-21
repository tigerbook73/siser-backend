<?php
/**
 * LicenseSharingStatus
 */
namespace Tests\Models;

/**
 * LicenseSharingStatus
 */
class LicenseSharingStatus
{
    /**
     * Possible values of this enum
     */
    const ACTIVE = 'active';

    const VOID = 'void';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ACTIVE,
            self::VOID
        ];
    }
}
