<?php
/**
 * CouponCreate
 */
namespace Tests\Models;

/**
 * CouponCreate
 */
class CouponCreate {

    /** @var string $name for free-trial coupon, name will be the plan name, for percentage coupon, name will be appended to the plan&#39;s name*/
    public $name = "";

    /** @var string $product_name coupon can only be applied to the plan with same product name*/
    public $product_name = "";

    /** @var string $type */
    public $type = "";

    /** @var string $coupon_event a event code associated with the coupon*/
    public $coupon_event = "";

    /** @var string $discount_type */
    public $discount_type = "";

    /** @var float $percentage_off must be 100 for free-trial coupon*/
    public $percentage_off = 0;

    /** @var string $interval */
    public $interval = "";

    /** @var int $interval_count For longterm interval, interval_count must be 0*/
    public $interval_count = 0;

    /** @var \DateTime $start_date when start_date &lt;&#x3D; today(), coupon will be activated immediately.*/
    public $start_date;

    /** @var \DateTime $end_date when end_date &lt; today() and coupon is active, coupon will be deactivated immediately.*/
    public $end_date;

    /** @var string $status */
    public $status = "";

    /** @var string $code */
    public $code = "";

}
