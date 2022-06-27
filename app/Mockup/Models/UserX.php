<?php
/**
 * User
 */
namespace App\Mockup\Models;

/**
 * User
 */
class UserX {

    /** @var int $id */
    public $id = 0;

    /** @var string $name same as the username field in coginto*/
    public $name = "";

    /** @var string $full_name */
    public $full_name = "";

    /** @var string $email */
    public $email = "";

    /** @var string $country */
    public $country = "";

    /** @var string $language */
    public $language = "";

    /** @var string $cognito_id cognito sub*/
    public $cognito_id = "";

    /** @var int $subscription_level */
    public $subscription_level = \App\Mockup\Models\SubscriptionLevel::NUMBER_0;

    /** @var string[] $roles */
    public $roles = [];

}
