<?php
/**
 * CouponUpdateCondition
 */
namespace Tests\Models;

/**
 * CouponUpdateCondition
 */
class CouponUpdateCondition {

    /** @var bool $new_customer_only whether coupon is only valid for the customer who place an order for the first time*/
    public $new_customer_only = false;

    /** @var bool $new_subscription_only whether coupon is only valid for purchase a new subscription, but not a upgrade subscription*/
    public $new_subscription_only = false;

    /** @var bool $upgrade_only whether coupon is only valid for purchase a upgrade subscription, but not a new subscription*/
    public $upgrade_only = false;

}
