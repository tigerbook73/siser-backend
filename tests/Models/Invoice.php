<?php
/**
 * Invoice
 */
namespace Tests\Models;

/**
 * Invoice
 */
class Invoice {

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var int $subscription_id */
    public $subscription_id = 0;

    /** @var int $period */
    public $period = 0;

    /** @var \DateTime $period_start_date */
    public $period_start_date;

    /** @var \DateTime $period_end_date */
    public $period_end_date;

    /** @var string $currency */
    public $currency = "";

    /** @var \Tests\Models\Plan $plan_info */
    public $plan_info;

    /** @var \Tests\Models\Coupon $coupon_info */
    public $coupon_info;

    /** @var \Tests\Models\ProcessingFee $processing_fee_info */
    public $processing_fee_info;

    /** @var float $subtotal price - discount + processing_fee*/
    public $subtotal = 0;

    /** @var float $total_tax */
    public $total_tax = 0;

    /** @var float $total_amount */
    public $total_amount = 0;

    /** @var \DateTime $invoice_date */
    public $invoice_date;

    /** @var string $pdf_file */
    public $pdf_file = "";

    /** @var \Tests\Models\InvoiceDR $dr */
    public $dr;

    /** @var string $status */
    public $status = "";

}
