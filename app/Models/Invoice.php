<?php

namespace App\Models;

use App\Models\Base\Invoice as BaseInvoice;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;

class Invoice extends BaseInvoice
{
  use TraitStatusTransition {
    setStatus as protected traitSetStatus;
  }
  use TraitDrAttr;

  // status -- see invoice.md
  public const STATUS_INIT            = 'init';
  public const STATUS_OPEN            = 'open';   // TODO: remove this status
  public const STATUS_PENDING         = 'pending';
  public const STATUS_CANCELLED       = 'cancelled';
  public const STATUS_PROCESSING      = 'processing';   // order.accepted or subscription.extended
  public const STATUS_FAILED          = 'failed';
  public const STATUS_COMPLETED       = 'completed';
  public const STATUS_REFUNDING       = 'refunding';
  public const STATUS_REFUND_FAILED   = 'refund-failed';
  public const STATUS_REFUNDED        = 'refunded';
  public const STATUS_PARTLY_REFUNDED = 'partly-refunded';

  // sub status
  public const SUB_STATUS_NONE        = 'none';
  public const SUB_STATUS_TO_REFUND   = 'to_refund'; // only for SUB_STATUS_PROCESSING

  // dr attributes
  public const DR_FILE_ID           = 'file_id';
  public const DR_INVOICE_ID        = 'invoice_id';
  public const DR_ORDER_ID          = 'order_id';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_start_date'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_end_date'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_id_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_refunded'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_date'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'pdf_file'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'credit_memos'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'sub_status'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function setStatus(string $status, Carbon $time = null): self
  {
    $this->traitSetStatus($status, $time);
    $this->sub_status = self::SUB_STATUS_NONE;
    return $this;
  }

  public function setSubStatus(string $subStatus = self::SUB_STATUS_NONE, Carbon $time = null): self
  {
    $this->sub_status = $subStatus;
    if ($subStatus != self::SUB_STATUS_NONE) {
      $status_transitions = $this->status_transitions ?? [];
      $status_transitions[$this->status . '/' . $subStatus] = $time ?? now();
      $this->status_transitions = $status_transitions;
    }
    return $this;
  }

  public function getSubStatus(): string
  {
    return $this->sub_status ?? self::SUB_STATUS_NONE;
  }

  public function getDrFileId(): string|null
  {
    return $this->getDrAttr(self::DR_FILE_ID);
  }

  public function getDrInvoiceId(): string|null
  {
    return $this->getDrAttr(self::DR_INVOICE_ID);
  }

  public function getDrOrderId(): string|null
  {
    return $this->getDrAttr(self::DR_ORDER_ID);
  }

  public function setDrFileId(string $drFileId): self
  {
    return $this->setDrAttr(self::DR_FILE_ID, $drFileId);
  }

  public function setDrInvoiceId(string $drInvoiceId): self
  {
    $this->dr_invoice_id = $drInvoiceId;
    return $this->setDrAttr(self::DR_INVOICE_ID, $drInvoiceId);
  }

  public function setDrOrderId(string|null $drOrderId): self
  {
    $this->dr_order_id = $drOrderId;
    return $this->setDrAttr(self::DR_ORDER_ID, $drOrderId);
  }

  static public function findByDrOrderId(string $drOrderId): Invoice|null
  {
    return self::where('dr_order_id', $drOrderId)->first();
  }

  public function isCompleted(): bool
  {
    return $this->status == self::STATUS_COMPLETED ||
      $this->status == self::STATUS_REFUNDED ||
      $this->status == self::STATUS_PARTLY_REFUNDED ||
      $this->status == self::STATUS_REFUND_FAILED ||
      $this->status == self::STATUS_REFUNDING;
  }

  public function addCreditMemo(string $fileId, string $url)
  {
    $creditMemos = $this->credit_memos ?? [];

    $found = $this->findCreditMemoByFileId($fileId);
    if ($found !== false) {
      $creditMemos[$found] = [
        'file_id'     => $fileId,
        'url'         => $url,
        'created_at'  => now(),
      ];
    } else {
      $creditMemos[] = [
        'file_id'     => $fileId,
        'url'         => $url,
        'created_at'  => now(),
      ];
    }
    $this->credit_memos = $creditMemos;
    return $this;
  }

  public function findCreditMemoByFileId(string $fileId): int|false
  {
    $creditMemos = $this->credit_memos ?? [];
    $count = count($creditMemos);

    for ($index = 0; $index < $count; $index++) {
      if ($creditMemos[$index]['file_id'] ?? "" == $fileId) {
        return $index;
      }
    }

    return false;
  }

  public function getActiveRefund(): Refund|null
  {
    return $this->refunds()->where('status', Refund::STATUS_PENDING)->first();
  }

  public function fillBasic(Subscription $subscription, bool $next = false): self
  {
    // static part
    $this->user_id             = $subscription->user_id;
    $this->subscription_id     = $subscription->id;
    $this->currency            = $subscription->currency;

    $this->billing_info        = $subscription->billing_info;
    $this->tax_id_info         = $subscription->tax_id_info;

    if (!$next) {
      // first invoice
      $this->plan_info         = $subscription->plan_info;
      $this->coupon_info       = $subscription->coupon_info;
    } else {
      // renew invoice
      $this->plan_info         = $subscription->next_invoice['plan_info'];
      $this->coupon_info       = $subscription->next_invoice['coupon_info'];
    }

    // dynamic part
    $this->payment_method_info = $subscription->payment_method_info;

    return $this;
  }

  public function fillPeriod(Subscription $subscription, bool $next = false): self
  {
    if ($next) {
      $this->period             = $subscription->next_invoice['current_period'];
      $this->period_start_date  = $subscription->next_invoice['current_period_start_date'];
      $this->period_end_date    = $subscription->next_invoice['current_period_end_date'];
      $this->invoice_date       = $subscription->next_invoice_date;
    } else {
      $this->period             = $subscription->current_period;
      $this->period_start_date  = $subscription->current_period_start_date;
      $this->period_end_date    = $subscription->current_period_end_date;
      $this->invoice_date       = now();
    }
    return $this;
  }

  public function fillFromDrObject(DrCheckout|DrOrder|DrInvoice $drObject): self
  {
    // Note: DrCheckout, DrOrder and DrInvoice has same following memeber functions

    $this->subtotal = $drObject->getSubtotal();
    $this->total_tax = $drObject->getTotalTax();
    $this->total_amount = $drObject->getTotalAmount();

    $source = $drObject->getPayment()->getSources()[0] ?? null;
    if ($source) {
      $paymentMethod = $source->getGooglePay() ?? $source->getCreditCard();
      $display_data =      $paymentMethod ? [
        'brand'             => $paymentMethod->getBrand(),
        'last_four_digits'  => $paymentMethod->getLastFourDigits(),
        'expiration_year'   => $paymentMethod->getExpirationYear(),
        'expiration_month'  => $paymentMethod->getExpirationMonth(),
      ] : null;
      $this->payment_method_info = [
        'type'          => $source->getType(),
        'display_data'  => $display_data,
        'dr'            => ['source_id' => $source->getId()],
      ];
    }

    return $this;
  }
}
