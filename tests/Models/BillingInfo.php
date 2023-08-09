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

    /** @var string $email */
    public $email = "";

    /** @var \Tests\Models\Address $address */
    public $address;

    /** @var \Tests\Models\BillingInfoCreateTaxId $tax_id */
    public $tax_id;

    /** @var string $language */
    public $language = "";

    /** @var string $locale */
    public $locale = "";

}
