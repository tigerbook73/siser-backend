<?php
/**
 * CouponCreate
 */
namespace Tests\Models;

/**
 * CouponCreate
 */
class CouponCreate {

    /** @var string $code */
    public $code = "";

    /** @var string $description */
    public $description = "";

    /** @var \Tests\Models\CouponCreateCondition $condition */
    public $condition;

    /** @var float $percentage_off */
    public $percentage_off = 0;

    /** @var int $period months. 0 means permenant*/
    public $period = 0;

    /** @var \DateTime $start_date */
    public $start_date;

    /** @var \DateTime $end_date */
    public $end_date;

}
