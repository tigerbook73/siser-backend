<?php
/**
 * LicenseSharingInvitationStatus
 */
namespace Tests\Models;

/**
 * LicenseSharingInvitationStatus
 */
class LicenseSharingInvitationStatus
{
    /**
     * Possible values of this enum
     */
    const OPEN = 'open';

    const ACCEPTED = 'accepted';

    const CANCELLED = 'cancelled';

    const REVOKED = 'revoked';

    const EXPIRED = 'expired';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::OPEN,
            self::ACCEPTED,
            self::CANCELLED,
            self::REVOKED,
            self::EXPIRED
        ];
    }
}
