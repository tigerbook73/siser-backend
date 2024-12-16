<?php
/**
 * SubscriptionCancelRequest
 */
namespace Tests\Models;

/**
 * SubscriptionCancelRequest
 */
class SubscriptionCancelRequest {

    /** @var bool $immediate cancel immediately or at the end of the current period. when refund is true, this value is ignored.*/
    public $immediate = false;

}
