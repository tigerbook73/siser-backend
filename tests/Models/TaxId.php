<?php
/**
 * TaxId
 */
namespace Tests\Models;

/**
 * TaxId
 */
class TaxId {

    /** @var string $type */
    public $type = "";

    /** @var string $value */
    public $value = "";

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var string $country A [two-letter Alpha-2 country code](https://www.iban.com/country-codes) as described in the [ISO 3166](https://www.iso.org/iso-3166-country-codes.html) international standard.*/
    public $country = "";

    /** @var string $customer_type */
    public $customer_type = "";

    /** @var string $status */
    public $status = "";

}
