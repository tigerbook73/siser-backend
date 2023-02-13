<?php
/**
 * DesignPlanCreate
 */
namespace Tests\Models;

/**
 * DesignPlanCreate
 */
class DesignPlanCreate {

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

}
