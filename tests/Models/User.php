<?php
/**
 * User
 */
namespace Tests\Models;

/**
 * User
 */
class User {

    /** @var int $id */
    public $id = 0;

    /** @var string $name same as the username field in coginto*/
    public $name = "";

    /** @var string $email */
    public $email = "";

    /** @var string $full_name */
    public $full_name = "";

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

    /** @var string $timezone */
    public $timezone = "";

    /** @var string $cognito_id cognito sub*/
    public $cognito_id = "";

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var int $machine_count */
    public $machine_count = 0;

    /** @var int $seat_count */
    public $seat_count = 0;

    /** @var string $type */
    public $type = "";

}
