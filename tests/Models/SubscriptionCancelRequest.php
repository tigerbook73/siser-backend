<?php
/**
 * SubscriptionCancelRequest
 */
namespace Tests\Models;

/**
 * SubscriptionCancelRequest
 */
class SubscriptionCancelRequest {

    /** @var bool $refund refund the current period or not. if refunded, the subscription will be terminated immediatelyl. otherwise, the subscription will be terminated at the end of the current period.*/
    public $refund = false;

    /** @var bool $immediate cancel immediately or at the end of the current period. when refund is true, this value is ignored.*/
    public $immediate = false;

}
