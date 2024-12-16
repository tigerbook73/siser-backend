<?php
/**
 * RefundItemType
 */
namespace Tests\Models;

/**
 * RefundItemType
 */
class RefundItemType
{
    /**
     * Possible values of this enum
     */
    const SUBSCRIPTION = 'subscription';

    const LICENSE = 'license';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SUBSCRIPTION,
            self::LICENSE
        ];
    }
}
