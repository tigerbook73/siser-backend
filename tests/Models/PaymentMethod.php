<?php
/**
 * PaymentMethod
 */
namespace Tests\Models;

/**
 * PaymentMethod
 */
class PaymentMethod {

    /** @var int $id */
    public $id = 0;

    /** @var string $type */
    public $type = "";

    /** @var \Tests\Models\CreditCard $credit_card */
    public $credit_card;

    /** @var object $paypal_billing */
    public $paypal_billing;

    /** @var string $provider_id the payment-method id of the Provider, e.g. DR::source_id*/
    public $provider_id = "";

}
