<?php

namespace App\Models;

use App\Models\Base\Refund as BaseRefund;

class Refund extends BaseRefund
{
  use TraitDrAttr;
  use TraitStatusTransition;

  // status -- see invoice.md
  public const STATUS_PENDING       = 'pending';
  public const STATUS_FAILED        = 'failed';
  public const STATUS_COMPLETED     = 'completed';

  // dr attributes
  public const DR_REFUND_ID         = 'refund_id';
  public const DR_ORDER_ID          = 'order_id';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_id'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'amount'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'reason'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];

  public function getDrRefundId()
  {
    return $this->getDrAttr(self::DR_REFUND_ID);
  }

  public function setDrRefundId(string $drRefundId)
  {
    $this->dr_refund_id = $drRefundId;
    return $this->setDrAttr(self::DR_REFUND_ID, $drRefundId);
  }

  public function getDrOrderId()
  {
    return $this->getDrAttr(self::DR_ORDER_ID);
  }

  public function setDrOrderId(string $drOrderId)
  {
    return $this->setDrAttr(self::DR_ORDER_ID, $drOrderId);
  }

  /**
   * create a new Refund model (without saving to database) from Invoice
   */
  static public function newFromInvoice(Invoice $invoice, float $amount = 0, string $reason = null): Refund
  {
    $refund = new self();
    $refund->user_id              = $invoice->user_id;
    $refund->subscription_id      = $invoice->subscription_id;
    $refund->invoice_id           = $invoice->id;
    $refund->currency             = $invoice->currency;
    $refund->amount               = ($amount > 0 && $amount < ($invoice->total_amount - $invoice->total_refunded)) ?
      $amount :
      $invoice->total_amount - $invoice->total_refunded;
    $refund->payment_method_info  = $invoice->payment_method_info;
    $refund->reason               = $reason ?? "";
    $refund->setDrOrderId($invoice->getDrOrderId());
    $refund->setStatus(self::STATUS_PENDING);
    return $refund;
  }

  static public function findByDrRefundId(string $drRefundId): ?Refund
  {
    return self::where('dr_refund_id', $drRefundId)->first();
  }
}
