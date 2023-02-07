<?php
/**
 * DesignPrice
 */
namespace Tests\Models;

/**
 * DesignPrice
 */
class DesignPrice {

    /** @var string $country A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country = "";

    /** @var string $currency */
    public $currency = "";

    /** @var float $price */
    public $price = 0;

}
