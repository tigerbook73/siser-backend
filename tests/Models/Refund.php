<?php
/**
 * Refund
 */
namespace Tests\Models;

/**
 * Refund
 */
class Refund {

    /** @var \Tests\Models\ID $id */
    public $id;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var int $subscription_id */
    public $subscription_id = 0;

    /** @var int $invoice_id */
    public $invoice_id = 0;

    /** @var string $currency */
    public $currency = "";

    /** @var \Tests\Models\InvoiceItem[] $items */
    public $items = [];

    /** @var float $amount */
    public $amount = 0;

    /** @var string $reason */
    public $reason = "";

    /** @var \Tests\Models\PaymentMethodInfo $payment_method_info */
    public $payment_method_info;

    /** @var array<string,mixed> $dr */
    public $dr;

    /** @var string $status */
    public $status = "";

}
