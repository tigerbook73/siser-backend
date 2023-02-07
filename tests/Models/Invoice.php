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

    /** @var string $currency */
    public $currency = "";

    /** @var \Tests\Models\InvoicePlan $plan */
    public $plan;

    /** @var \Tests\Models\InvoiceCoupon $coupon */
    public $coupon;

    /** @var \Tests\Models\InvoiceProcessingFee $processing_fee */
    public $processing_fee;

    /** @var float $amount price - discount + processing_fee*/
    public $amount = 0;

    /** @var float $tax */
    public $tax = 0;

    /** @var float $total_amount */
    public $total_amount = 0;

    /** @var \DateTime $invoice_date */
    public $invoice_date;

    /** @var string $pdf_file */
    public $pdf_file = "";

    /** @var string $status */
    public $status = "";

}
