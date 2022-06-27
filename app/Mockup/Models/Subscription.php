<?php
/**
 * Subscription
 */
namespace App\Mockup\Models;

/**
 * Subscription
 */
class Subscription {

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var \App\Mockup\Models\Plan $plan */
    public $plan;

    /** @var \DateTime $start_date */
    public $start_date;

    /** @var \DateTime|null $end_date */
    public $end_date;

    /** @var string $status */
    public $status = "";

}
