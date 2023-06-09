<?php
/**
 * CreditCard
 */
namespace Tests\Models;

/**
 * CreditCard
 */
class CreditCard {

    /** @var string $brand The card brand.*/
    public $brand = "";

    /** @var string $last_four_digits The last four digits of the card number.*/
    public $last_four_digits = "";

    /** @var int $expiration_year */
    public $expiration_year = 0;

    /** @var int $expiration_month */
    public $expiration_month = 0;

}
