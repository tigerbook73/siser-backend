<?php
/**
 * Plan
 */
namespace App\Mockup\Models;

/**
 * Plan
 */
class Plan {

    /** @var int $id */
    public $id = 0;

    /** @var string $name */
    public $name = "";

    /** @var string $catagory */
    public $catagory = "";

    /** @var string $description LDS Basic plan for machine owner*/
    public $description = "";

    /** @var int $subscription_level */
    public $subscription_level = \App\Mockup\Models\SubscriptionLevel::NUMBER_0;

    /** @var string $contract_term */
    public $contract_term = "";

    /** @var \App\Mockup\Models\PriceWithCurrency[] $price */
    public $price = [];

    /** @var bool $auto_renew */
    public $auto_renew = true;

    /** @var string $url */
    public $url = "";

    /** @var string $status */
    public $status = "";

}
