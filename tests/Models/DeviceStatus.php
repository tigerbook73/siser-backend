<?php
/**
 * DeviceStatus
 */
namespace Tests\Models;

/**
 * DeviceStatus
 */
class DeviceStatus
{
    /**
     * Possible values of this enum
     */
    const ONLINE = 'online';

    const OFFLINE = 'offline';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ONLINE,
            self::OFFLINE
        ];
    }
}
