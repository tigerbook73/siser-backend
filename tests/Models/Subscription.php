<?php
/**
 * Subscription
 */
namespace Tests\Models;

/**
 * Subscription
 */
class Subscription {

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var int $coupon_id */
    public $coupon_id = 0;

    /** @var \Tests\Models\BillingInfo $billing_info */
    public $billing_info;

    /** @var \Tests\Models\Plan $plan_info */
    public $plan_info;

    /** @var \Tests\Models\Coupon $coupon_info */
    public $coupon_info;

    /** @var \Tests\Models\SubscriptionProcessingFeeInfo $processing_fee_info */
    public $processing_fee_info;

    /** @var string $currency */
    public $currency = "";

    /** @var float $price beautified price (beautified price)*/
    public $price = 0;

    /** @var float $processing_fee */
    public $processing_fee = 0;

    /** @var float $tax */
    public $tax = 0;

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var int $current_period */
    public $current_period = 0;

    /** @var \DateTime $start_date */
    public $start_date;

    /** @var \DateTime $end_date */
    public $end_date;

    /** @var \DateTime $current_period_start_date */
    public $current_period_start_date;

    /** @var \DateTime $current_period_end_date */
    public $current_period_end_date;

    /** @var \DateTime $next_invoice_date */
    public $next_invoice_date;

    /** @var \Tests\Models\SubscriptionDR $dr */
    public $dr;

    /** @var string $status */
    public $status = "";

    /** @var string $sub_status */
    public $sub_status = "";

    /** @var string $stop_reason */
    public $stop_reason = "";

}
