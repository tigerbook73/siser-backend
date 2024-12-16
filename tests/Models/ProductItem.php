<?php
/**
 * ProductItem
 */
namespace Tests\Models;

/**
 * ProductItem
 */
class ProductItem {

    /** @var string $category */
    public $category = "";

    /** @var string $name */
    public $name = "";

    /** @var int $quantity always 1*/
    public $quantity = 0;

    /** @var float $price price (tax exclusive)*/
    public $price = 0;

    /** @var float $discount */
    public $discount = 0;

    /** @var float|null $tax */
    public $tax = null;

    /** @var float|null $amount price after tax*/
    public $amount = null;

    /** @var string $dr_order_id */
    public $dr_order_id = "";

    /** @var float $available_to_refund_amount */
    public $available_to_refund_amount = 0;

}
