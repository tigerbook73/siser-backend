<?php
/**
 * PaymentMethod
 */
namespace App\Mockup\Models;

/**
 * PaymentMethod
 */
class PaymentMethod {

    /** @var int $id */
    public $id = 0;

    /** @var string $type */
    public $type = "";

    /** @var string $account_hint masked account number or name or...*/
    public $account_hint = "";

    /** @var string $status */
    public $status = "";

}
