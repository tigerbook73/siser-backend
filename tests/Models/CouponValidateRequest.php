<?php
/**
 * CouponValidateRequest
 */
namespace Tests\Models;

/**
 * CouponValidateRequest
 */
class CouponValidateRequest {

    /** @var string $code coupon code*/
    public $code = "";

    /** @var string $country A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country = "";

    /** @var int $plan_id */
    public $plan_id = 0;

    /** @var int $user_id user id*/
    public $user_id = 0;

}
