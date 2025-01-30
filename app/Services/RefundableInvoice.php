<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Refund;

class RefundableInvoice
{
  public function __construct(public Invoice $invoice)
  {
    //
  }

  public function getRefundableAmount(): float
  {
    return $this->invoice->available_to_refund_amount;
  }
}
