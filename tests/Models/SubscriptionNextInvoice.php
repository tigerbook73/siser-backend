<?php
/**
 * SubscriptionNextInvoice
 */
namespace Tests\Models;

/**
 * SubscriptionNextInvoice
 */
class SubscriptionNextInvoice {

    /** @var int $current_period */
    public $current_period = 0;

    /** @var \DateTime $current_period_start_date */
    public $current_period_start_date;

    /** @var \DateTime $current_period_end_date */
    public $current_period_end_date;

    /** @var \Tests\Models\PlanInfo $plan_info */
    public $plan_info;

    /** @var \Tests\Models\CouponInfo $coupon_info */
    public $coupon_info;

    /** @var float $price beautified price (beautified price)*/
    public $price = 0;

    /** @var float $subtotal */
    public $subtotal = 0;

    /** @var float $tax_rate */
    public $tax_rate = 0;

    /** @var float $total_tax */
    public $total_tax = 0;

    /** @var float $total_amount */
    public $total_amount = 0;

}
