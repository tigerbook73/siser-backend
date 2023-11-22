<?php
/**
 * XRayStatisticRecordItem
 */
namespace Tests\Models;

/**
 * XRayStatisticRecordItem
 */
class XRayStatisticRecordItem {

    /** @var string $country */
    public $country = "";

    /** @var string $currency */
    public $currency = "";

    /** @var int $subscription_level */
    public $subscription_level = 0;

    /** @var string $plan */
    public $plan = "";

    /** @var string $coupon */
    public $coupon = "";

    /** @var string $machine_owner */
    public $machine_owner = "";

    /** @var int $count */
    public $count = 0;

    /** @var int $activated subscription activated on this date*/
    public $activated = 0;

    /** @var int $cancelled subscription activated on this date*/
    public $cancelled = 0;

    /** @var int $converted subscription converted from free-trial on this date*/
    public $converted = 0;

    /** @var int $extended subscription extented on this date*/
    public $extended = 0;

    /** @var int $failed subscription failed on this date*/
    public $failed = 0;

    /** @var int $stopped subscription stopped on this date*/
    public $stopped = 0;

}
