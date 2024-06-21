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

    /** @var float $price price (tax inclusive or exclusive)*/
    public $price = 0;

    /** @var float|null $tax */
    public $tax = null;

    /** @var float|null $amount price after tax*/
    public $amount = null;

}
