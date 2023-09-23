<?php
/**
 * PlanBasic
 */
namespace Tests\Models;

/**
 * PlanBasic
 */
class PlanBasic {

    /** @var string $name */
    public $name = "";

    /** @var string $product_name name of the software product*/
    public $product_name = "";

    /** @var string $interval */
    public $interval = "";

    /** @var string $description LDS Basic plan for machine owner*/
    public $description = "";

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var string $url */
    public $url = "";

}
