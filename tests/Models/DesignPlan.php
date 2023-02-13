<?php
/**
 * DesignPlan
 */
namespace Tests\Models;

/**
 * DesignPlan
 */
class DesignPlan {

    /** @var int $id */
    public $id = 0;

    /** @var string $name */
    public $name = "";

    /** @var string $catagory */
    public $catagory = "";

    /** @var string $description LDS Basic plan for machine owner*/
    public $description = "";

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var string $url */
    public $url = "";

    /** @var \Tests\Models\Price[] $price_list */
    public $price_list = [];

    /** @var string $status */
    public $status = "";

}
