<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Refund;

class RefundableInvoice
{
  public function __construct(public Invoice $invoice, public string $itemType)
  {
    //
  }

  public function getRefundableAmount(): float
  {
    if ($this->itemType == Refund::ITEM_SUBSCRIPTION) {
      return $this->invoice->available_to_refund_amount;
    }

    // refund license item
    $item = $this->invoice->findLicenseItem();
    if (!$item) {
      throw new \Exception('itemType and invoice does not match');
    }

    // Note: the calculation is not accurate for not completed order, the available_to_refund_amount is 0
    return min(
      $this->invoice->available_to_refund_amount,
      $item['available_to_refund_amount'] ?? ($item['price'] + $item['tax'])
    );
  }
}
