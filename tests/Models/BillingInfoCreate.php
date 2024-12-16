<?php
/**
 * BillingInfoCreate
 */
namespace Tests\Models;

/**
 * BillingInfoCreate
 */
class BillingInfoCreate {

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

}
