<?php
/**
 * LdsLicense
 */
namespace Tests\Models;

/**
 * LdsLicense
 */
class LdsLicense {

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var int $subscription_level */
    public $subscription_level = \Tests\Models\SubscriptionLevel::NUMBER_0;

    /** @var int $license_count */
    public $license_count = 0;

    /** @var int $license_free */
    public $license_free = 0;

    /** @var int $license_used */
    public $license_used = 0;

    /** @var int $latest_expires_at */
    public $latest_expires_at = 0;

    /** @var int $lastest_expires_at */
    public $lastest_expires_at = 0;

    /** @var \Tests\Models\Device[] $devices */
    public $devices = [];

}
