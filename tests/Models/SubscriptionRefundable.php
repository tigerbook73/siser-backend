<?php
/**
 * SubscriptionRefundable
 */
namespace Tests\Models;

/**
 * SubscriptionRefundable
 */
class SubscriptionRefundable {

    /** @var string $result */
    public $result = "";

    /** @var string $reason only valid when type is not_refundable*/
    public $reason = "";

    /** @var float $refundable_amount */
    public $refundable_amount = 0;

    /** @var \Tests\Models\Subscription $subscription */
    public $subscription;

    /** @var \Tests\Models\Invoice[] $invoices */
    public $invoices = [];

}
