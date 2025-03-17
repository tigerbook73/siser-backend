<?php
/**
 * InvoiceItem
 */
namespace Tests\Models;

/**
 * InvoiceItem
 */
class InvoiceItem {

    /** @var string $name */
    public $name = "";

    /** @var string $currency */
    public $currency = "";

    /** @var float $price */
    public $price = 0;

    /** @var float $discount */
    public $discount = 0;

    /** @var float $tax */
    public $tax = 0;

    /** @var float $amount */
    public $amount = 0;

    /** @var int $quantity */
    public $quantity = 0;

    /** @var int $plan_id */
    public $plan_id = 0;

    /** @var int $license_package_id */
    public $license_package_id = 0;

    /** @var int $license_quantity */
    public $license_quantity = 0;

    /** @var array<string,mixed> $meta */
    public $meta;

}
