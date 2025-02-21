<?php
/**
 * LicensePackage
 */
namespace Tests\Models;

/**
 * LicensePackage
 */
class LicensePackage {

    /** @var int $id */
    public $id = 0;

    /** @var string $type */
    public $type = "";

    /** @var string $name */
    public $name = "";

    /** @var \Tests\Models\LicensePackagePriceTable $price_table */
    public $price_table;

    /** @var string $status */
    public $status = "";

}
