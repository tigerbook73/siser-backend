<?php
/**
 * CouponInfo
 */
namespace Tests\Models;

/**
 * CouponInfo
 */
class CouponInfo {

    /** @var int $id */
    public $id = 0;

    /** @var string $code */
    public $code = "";

    /** @var string $name */
    public $name = "";

    /** @var string $product_name */
    public $product_name = "";

    /** @var string $type */
    public $type = "";

    /** @var string $coupon_event */
    public $coupon_event = "";

    /** @var string $discount_type */
    public $discount_type = "";

    /** @var float $percentage_off */
    public $percentage_off = 0;

    /** @var string $interval */
    public $interval = "";

    /** @var int $interval_size */
    public $interval_size = 0;

    /** @var int $interval_count */
    public $interval_count = 0;

}
