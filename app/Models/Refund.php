<?php

namespace App\Models;

use App\Models\Base\Refund as BaseRefund;

class Refund extends BaseRefund
{
  use TraitStatusTransition;

  // type
  public const ITEM_SUBSCRIPTION    = 'subscription';   // refund the whole subscription (may or may not include the license package)

  // status -- see invoice.md
  public const STATUS_PENDING       = 'pending';
  public const STATUS_FAILED        = 'failed';
  public const STATUS_COMPLETED     = 'completed';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_id'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'items'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'amount'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'reason'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];


  /**
   * create a new Refund model (without saving to database) from Invoice
   */
  static public function newFromInvoice(Invoice $invoice, float $amount, string $reason = null): Refund
  {
    if ($amount > $invoice->available_to_refund_amount) {
      throw new \Exception('Refund amount exceeds the available amount');
    }

    $items = [$invoice->findPlanItem()];

    $refund = new self();
    $refund->user_id              = $invoice->user_id;
    $refund->subscription_id      = $invoice->subscription_id;
    $refund->invoice_id           = $invoice->id;
    $refund->currency             = $invoice->currency;
    $refund->item_type            = Refund::ITEM_SUBSCRIPTION;  // obsolated
    $refund->items                = $items;
    $refund->amount               = $amount;
    $refund->payment_method_info  = $invoice->payment_method_info;
    $refund->reason               = $reason ?? "";
    $refund->setStatus(self::STATUS_PENDING);
    return $refund;
  }

  public function getMeta(): RefundMeta
  {
    return RefundMeta::from($this->meta);
  }

  public function setMeta(RefundMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleAdjustmentId(?string $paddleAdjustmentId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->adjustment_id = $paddleAdjustmentId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTransactionId(?string $paddleTransactionId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->transaction_id = $paddleTransactionId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTimestamp(?string $paddleTimestamp): self
  {
    $meta = $this->getMeta();
    $meta->paddle->paddle_timestamp = $paddleTimestamp;
    return $this->setMeta($meta);
  }
}
