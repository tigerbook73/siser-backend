<?php
/**
 * LicensePackageInfo
 */
namespace Tests\Models;

/**
 * LicensePackageInfo
 */
class LicensePackageInfo {

    /** @var int $id */
    public $id = 0;

    /** @var string $type */
    public $type = "";

    /** @var string $name */
    public $name = "";

    /** @var \Tests\Models\LicensePackagePriceStep[] $price_table */
    public $price_table = [];

    /** @var int $quantity */
    public $quantity = 0;

    /** @var float $price_rate price_rate to the plan price (plan_price * price_rate)*/
    public $price_rate = 0;

}
