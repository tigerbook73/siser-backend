<?php
/**
 * SoftwareCheckInRes
 */
namespace App\Mockup\Models;

/**
 * SoftwareCheckInRes
 */
class SoftwareCheckInRes {

    /** @var string $version must be same as requested version.*/
    public $version = "";

    /** @var string $device_id */
    public $device_id = "";

    /** @var int $subscription_level */
    public $subscription_level = \App\Mockup\Models\SubscriptionLevel::NUMBER_0;

    /** @var int $expires_at seconds since 1970/01/01 00:00:00 GMT*/
    public $expires_at = 0;

}
