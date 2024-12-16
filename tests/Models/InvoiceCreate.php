<?php
/**
 * InvoiceCreate
 */
namespace Tests\Models;

/**
 * InvoiceCreate
 */
class InvoiceCreate {

    /** @var int $subscription_id */
    public $subscription_id = 0;

    /** @var string $type */
    public $type = "";

    /** @var int $license_package_id required for new-license-package type*/
    public $license_package_id = 0;

    /** @var int $license_count new license count. required both for new-license-package and increase-license-number*/
    public $license_count = 0;

}
