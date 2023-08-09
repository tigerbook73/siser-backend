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

    /** @var \Tests\Models\TaxIdInfo $tax_id_info */
    public $tax_id_info;

    /** @var \Tests\Models\Plan $plan_info */
    public $plan_info;

    /** @var \Tests\Models\Coupon $coupon_info */
    public $coupon_info;

    /** @var string $currency */
    public $currency = "";

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

    /** @var \Tests\Models\SubscriptionNextInvoice $next_invoice */
    public $next_invoice;

    /** @var \Tests\Models\SubscriptionDR $dr */
    public $dr;

    /** @var string $status */
    public $status = "";

    /** @var string $sub_status */
    public $sub_status = "";

    /** @var string $stop_reason */
    public $stop_reason = "";

}
