<?php

namespace App\Models;

use App\Models\Base\Refund as BaseRefund;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrRefund;

class Refund extends BaseRefund
{
  use TraitDrAttr;
  use TraitStatusTransition;

  // type
  public const ITEM_SUBSCRIPTION    = 'subscription';   // refund the whole subscription (may or may not include the license package)
  public const ITEM_LICENSE         = 'license';        // refund the license package only

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
    'item_type'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
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
  static public function newFromInvoice(Invoice $invoice, string $itemType, float $amount, string $reason = null): Refund
  {
    if ($amount > $invoice->available_to_refund_amount) {
      throw new \Exception('Refund amount exceeds the available amount');
    }

    $item = null;
    if ($itemType === self::ITEM_LICENSE) {
      $item = $invoice->findLicenseItem();
      if (!$item) {
        throw new \Exception('itemType and invoice does not match');
      }
      if ($item['available_to_refund_amount'] < $amount) {
        throw new \Exception('Refund amount exceeds the item\'s available amount');
      }
    }

    $refund = new self();
    $refund->user_id              = $invoice->user_id;
    $refund->subscription_id      = $invoice->subscription_id;
    $refund->invoice_id           = $invoice->id;
    $refund->currency             = $invoice->currency;
    $refund->item_type            = $itemType;
    $refund->items                = $item ? [$item] : [];
    $refund->amount               = $amount;
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

  public function fillFromDrObject(DrRefund $drRefund): self
  {
    $this->setDrRefundId($drRefund->getId());
    $this->amount = $drRefund->getAmount() ?? array_reduce($drRefund->getItems() ?? [], fn($carry, $item) => $carry + $item->getAmount(), 0);

    return $this;
  }
}
