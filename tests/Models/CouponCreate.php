<?php
/**
 * CouponCreate
 */
namespace Tests\Models;

/**
 * CouponCreate
 */
class CouponCreate {

    /** @var string $description */
    public $description = "";

    /** @var \Tests\Models\CouponUpdateCondition $condition */
    public $condition;

    /** @var float $percentage_off */
    public $percentage_off = 0;

    /** @var int $period months. 0 means permenant*/
    public $period = 0;

    /** @var \DateTime $start_date when start_date &lt;&#x3D; today(), coupon will be activated immediately.*/
    public $start_date;

    /** @var \DateTime $end_date when end_date &lt; today() and coupon is active, coupon will be deactivated immediately.*/
    public $end_date;

    /** @var string $code */
    public $code = "";

}
