<?php
/**
 * DesignPlanStatus
 */
namespace Tests\Models;

/**
 * DesignPlanStatus
 */
class DesignPlanStatus
{
    /**
     * Possible values of this enum
     */
    const DRAFT = 'draft';

    const ACTIVE = 'active';

    const INACTIVE = 'inactive';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::DRAFT,
            self::ACTIVE,
            self::INACTIVE
        ];
    }
}
