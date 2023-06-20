<?php
/**
 * CountryCreate
 */
namespace Tests\Models;

/**
 * CountryCreate
 */
class CountryCreate {

    /** @var string $code A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $code = "";

    /** @var string $name */
    public $name = "";

    /** @var string $currency */
    public $currency = "";

}
