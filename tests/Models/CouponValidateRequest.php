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

    /** @var int $plan_id */
    public $plan_id = 0;

    /** @var string $country A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country = "";

    /** @var bool $new_customer whether this is a new customer*/
    public $new_customer = false;

    /** @var bool $new_subscription whether this is a new subscription*/
    public $new_subscription = false;

    /** @var bool $upgrade_subscription whether this is a upgrade subscrption*/
    public $upgrade_subscription = false;

}
