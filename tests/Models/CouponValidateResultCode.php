<?php
/**
 * CouponValidateResultCode
 */
namespace Tests\Models;

/**
 * CouponValidateResultCode
 */
class CouponValidateResultCode
{
    /**
     * Possible values of this enum
     */
    const SUCCESS = 'success';

    const INVALID_CODE = 'invalid_code';

    const INVALID_PLAN = 'invalid_plan';

    const INVALID_LICENSE_QUANTITY = 'invalid_license_quantity';

    const FREE_TRIAL_NOT_ALLOWED = 'free_trial_not_allowed';

    const FREE_TRIAL_MORE_THAN_ONCE = 'free_trial_more_than_once';

    const NOT_APPLICABLE = 'not_applicable';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SUCCESS,
            self::INVALID_CODE,
            self::INVALID_PLAN,
            self::INVALID_LICENSE_QUANTITY,
            self::FREE_TRIAL_NOT_ALLOWED,
            self::FREE_TRIAL_MORE_THAN_ONCE,
            self::NOT_APPLICABLE
        ];
    }
}
