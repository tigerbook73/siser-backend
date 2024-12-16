<?php
/**
 * LicensePlan
 */
namespace Tests\Models;

/**
 * LicensePlan
 */
class LicensePlan {

    /** @var int $id */
    public $id = 0;

    /** @var string $product_name */
    public $product_name = "";

    /** @var string $interval */
    public $interval = "";

    /** @var int $interval_count */
    public $interval_count = 0;

    /** @var \Tests\Models\LicensePlanDetailsInner[] $details */
    public $details = [];

}
