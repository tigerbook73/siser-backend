<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * RefundableResult
 *
 * @property bool $refundable
 * @property string $reason
 * @property float $refundableAmount
 * @property RefundableInvoice[] $invoices
 */
class RefundableResult
{
  public bool $refundable = false;
  public string $reason = '';
  public float $refundableAmount = 0;
  public array $invoices = [];

  public function setRefundable(bool $refundable): RefundableResult
  {
    $this->refundable = $refundable;
    return $this;
  }

  public function isRefundable(): bool
  {
    return $this->refundable;
  }

  public function setReason(string $reason): RefundableResult
  {
    $this->reason = $reason;
    return $this;
  }

  public function getRefundableAmount(): float
  {
    return $this->refundableAmount;
  }

  public function getReason(): string
  {
    return $this->reason;
  }

  protected function updateRefundableAmount(): RefundableResult
  {
    $this->refundableAmount =
      round(
        array_reduce($this->invoices, fn($amount, $invoice) => $amount + $invoice->getRefundableAmount(), 0),
        2
      );
    return $this;
  }

  /**
   * @param Invoice|Invoice[] $invoice
   */
  public function setInvoices(Invoice|array $invoice): RefundableResult
  {
    $invoices = is_array($invoice) ? $invoice : [$invoice];
    $this->invoices = array_map(fn($invoice) => new RefundableInvoice($invoice), $invoices);
    $this->updateRefundableAmount();
    return $this;
  }

  /**
   * @param Invoice|Invoice[] $invoice
   */
  public function appendInvoices(Invoice|array $invoice): RefundableResult
  {
    $invoices = is_array($invoice) ? $invoice : [$invoice];
    $this->invoices = array_merge($this->invoices, array_map(fn($invoice) => new RefundableInvoice($invoice), $invoices));
    $this->updateRefundableAmount();
    return $this;
  }

  /**
   * @return RefundableInvoice[]
   */
  public function getInvoices(): array
  {
    return $this->invoices;
  }
}
