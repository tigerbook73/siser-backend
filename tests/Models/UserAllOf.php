<?php
/**
 * UserAllOf
 */
namespace Tests\Models;

/**
 * UserAllOf
 */
class UserAllOf {

    /** @var string $given_name */
    public $given_name = "";

    /** @var string $family_name */
    public $family_name = "";

    /** @var string $phone_number */
    public $phone_number = "";

    /** @var string $country_code A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country_code = "";

    /** @var string $language_code */
    public $language_code = "";

    /** @var string $cognito_id cognito sub*/
    public $cognito_id = "";

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var int $license_count */
    public $license_count = 0;

    /** @var bool $blacklisted */
    public $blacklisted = false;

}
