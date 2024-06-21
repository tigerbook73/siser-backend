<?php
/**
 * TaxIdStatus
 */
namespace Tests\Models;

/**
 * TaxIdStatus
 */
class TaxIdStatus
{
    /**
     * Possible values of this enum
     */
    const PENDING = 'pending';

    const VERIFIED = 'verified';

    const NOT_VALID = 'not_valid';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::PENDING,
            self::VERIFIED,
            self::NOT_VALID
        ];
    }
}
