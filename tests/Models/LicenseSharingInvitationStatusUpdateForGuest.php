<?php
/**
 * LicenseSharingInvitationStatusUpdateForGuest
 */
namespace Tests\Models;

/**
 * LicenseSharingInvitationStatusUpdateForGuest
 */
class LicenseSharingInvitationStatusUpdateForGuest
{
    /**
     * Possible values of this enum
     */
    const ACCEPTED = 'accepted';

    const CANCELLED = 'cancelled';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ACCEPTED,
            self::CANCELLED
        ];
    }
}
