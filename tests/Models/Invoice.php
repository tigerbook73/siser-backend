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

    /** @var \Tests\Models\BillingInfo $billing_info */
    public $billing_info;

    /** @var \Tests\Models\TaxIdInfo $tax_id_info */
    public $tax_id_info;

    /** @var \Tests\Models\PlanInfo $plan_info */
    public $plan_info;

    /** @var \Tests\Models\CouponInfo $coupon_info */
    public $coupon_info;

    /** @var \Tests\Models\PaymentMethodInfo $payment_method_info */
    public $payment_method_info;

    /** @var float $subtotal price - discount*/
    public $subtotal = 0;

    /** @var float $total_tax */
    public $total_tax = 0;

    /** @var float $total_amount */
    public $total_amount = 0;

    /** @var float $total_refunded */
    public $total_refunded = 0;

    /** @var \DateTime $invoice_date */
    public $invoice_date;

    /** @var string $pdf_file */
    public $pdf_file = "";

    /** @var \Tests\Models\InvoiceCreditMemo[] $credit_memos */
    public $credit_memos = [];

    /** @var \Tests\Models\InvoiceDR $dr */
    public $dr;

    /** @var string $status */
    public $status = "";

    /** @var string $sub_status */
    public $sub_status = "";

    /** @var string $dispute_status */
    public $dispute_status = "";

}
