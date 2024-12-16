<?php
/**
 * SubscriptionLicensePackageDecreaseRequest
 */
namespace Tests\Models;

/**
 * SubscriptionLicensePackageDecreaseRequest
 */
class SubscriptionLicensePackageDecreaseRequest {

    /** @var bool $immediate take effect immediately or at the end of the current period.*/
    public $immediate = false;

    /** @var int $license_count */
    public $license_count = 0;

}
