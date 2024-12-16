<?php
/**
 * BillingInfo
 */
namespace Tests\Models;

/**
 * BillingInfo
 */
class BillingInfo {

    /** @var string $first_name */
    public $first_name = "";

    /** @var string $last_name */
    public $last_name = "";

    /** @var string $phone */
    public $phone = "";

    /** @var string $customer_type individual or business*/
    public $customer_type = "";

    /** @var string $organization */
    public $organization = "";

    /** @var \Tests\Models\Address $address */
    public $address;

    /** @var string $language */
    public $language = "";

    /** @var float $user_id */
    public $user_id = 0;

    /** @var string $email */
    public $email = "";

    /** @var string $locale */
    public $locale = "";

    /** @var array<string,mixed> $meta */
    public $meta;

}
