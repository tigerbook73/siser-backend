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

    /** @var \Tests\Models\Subscription $subscription */
    public $subscription;

    /** @var \Tests\Models\Invoice $invoice */
    public $invoice;

}
