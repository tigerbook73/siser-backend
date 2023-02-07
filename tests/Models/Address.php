<?php
/**
 * Address
 */
namespace Tests\Models;

/**
 * Address
 */
class Address {

    /** @var string $line1 The first line of the address.*/
    public $line1 = "";

    /** @var string $line2 The second line of the address.*/
    public $line2 = "";

    /** @var string $city The city of the address.*/
    public $city = "";

    /** @var string $postcode The postal code of the address.*/
    public $postcode = "";

    /** @var string $state The state, county, province, or region.*/
    public $state = "";

    /** @var string $country A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country = "";

}
