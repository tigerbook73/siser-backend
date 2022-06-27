<?php
/**
 * Invoice
 */
namespace App\Mockup\Models;

/**
 * Invoice
 */
class Invoice {

    /** @var int $id */
    public $id = 0;

    /** @var int $user_id */
    public $user_id = 0;

    /** @var string $invoice_no */
    public $invoice_no = "";

    /** @var float $balance */
    public $balance = 0;

    /** @var string $service_period */
    public $service_period = "";

    /** @var \DateTime $issue_date */
    public $issue_date;

    /** @var \DateTime $due_date */
    public $due_date;

    /** @var \App\Mockup\Models\InvoiceItem[] $invoice_items */
    public $invoice_items = [];

}
