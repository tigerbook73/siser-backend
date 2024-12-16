<?php
/**
 * InvoiceDR
 */
namespace Tests\Models;

/**
 * InvoiceDR
 */
class InvoiceDR {

    /** @var string $order_id */
    public $order_id = "";

    /** @var string $invoice_id */
    public $invoice_id = "";

    /** @var string $file_id */
    public $file_id = "";

    /** @var string[] $invoice_id_list */
    public $invoice_id_list = [];

    /** @var string $checkout_payment_session_id only for non-subscription invoice*/
    public $checkout_payment_session_id = "";

}
