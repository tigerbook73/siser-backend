<?php
/**
 * SubscriptionRenewalInfo
 */
namespace Tests\Models;

/**
 * SubscriptionRenewalInfo
 */
class SubscriptionRenewalInfo {

    /** @var \Tests\Models\ID $id */
    public $id;

    /** @var int $subscription_id */
    public $subscription_id = 0;

    /** @var int $period */
    public $period = 0;

    /** @var \DateTime $start_at */
    public $start_at;

    /** @var \DateTime $expire_at */
    public $expire_at;

    /** @var \DateTime $first_reminder_at */
    public $first_reminder_at;

    /** @var \DateTime $final_reminder_at */
    public $final_reminder_at;

    /** @var string $status */
    public $status = "";

    /** @var string $sub_status */
    public $sub_status = "";

}
