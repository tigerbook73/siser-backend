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
  public const SUB_STATUS_TO_REFUND   = 'to_refund'; // only for STATUS_PROCESSING

  // dispute status
  public const DISPUTE_STATUS_NONE      = 'none';
  public const DISPUTE_STATUS_DISPUTING = 'disputing';
  public const DISPUTE_STATUS_DISPUTED  = 'disputed';

  // dr attributes
  public const DR_FILE_ID           = 'file_id';
  public const DR_INVOICE_ID        = 'invoice_id';
  public const DR_ORDER_ID          = 'order_id';
  public const DR_INVOICE_ID_LIST   = 'invoice_id_list';

  static protected $attributesOption = [
    'id'                          => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'                     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period'                      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_start_date'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_end_date'             => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_id_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_package_info'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'items'                       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'                   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_refunded'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_date'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'pdf_file'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'credit_memos'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'sub_status'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dispute_status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'dispute_status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
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

  public function getDrInvoiceIdList(): array
  {
    return $this->getDrAttr(self::DR_INVOICE_ID_LIST) ?? [];
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
    if ($this->dr_invoice_id == $drInvoiceId) {
      return $this;
    }

    $this->dr_invoice_id = $drInvoiceId;
    $this->setDrAttr(self::DR_INVOICE_ID, $drInvoiceId);

    $drInvoiceIdList = $this->getDrAttr(self::DR_INVOICE_ID_LIST) ?? [];
    $drInvoiceIdList[] = $drInvoiceId;
    $this->setDrAttr(self::DR_INVOICE_ID_LIST, $drInvoiceIdList);
    return $this;
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

  static public function findByDrInvoiceId(string $drInvoiceId): Invoice|null
  {
    return self::where('dr_invoice_id', $drInvoiceId)->first();
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
    $this->user_id                = $subscription->user_id;
    $this->subscription_id        = $subscription->id;
    $this->currency               = $subscription->currency;

    $this->billing_info           = $subscription->billing_info;
    $this->tax_id_info            = $subscription->tax_id_info;

    if (!$next) {
      // first invoice
      $this->plan_info            = $subscription->plan_info;
      $this->coupon_info          = $subscription->coupon_info;
      $this->license_package_info = $subscription->license_package_info;
      $this->items                = $subscription->items;
    } else {
      // renew invoice
      $this->plan_info            = $subscription->next_invoice['plan_info'];
      $this->coupon_info          = $subscription->next_invoice['coupon_info'];
      $this->license_package_info = $subscription->next_invoice['license_package_info'];
      $this->items                = $subscription->next_invoice['items'];
    }

    // dynamic part
    $this->payment_method_info    = $subscription->payment_method_info;

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
    if ($drObject instanceof DrOrder) {
      $this->setDrOrderId($drObject->getId());
    } else if ($drObject instanceof DrInvoice) {
      $this->setDrInvoiceId($drObject->getId());
    }

    // Note: DrCheckout, DrOrder and DrInvoice has same following memeber functions

    // fill items
    $this->items = ProductItem::buildItemsFromDrObject($drObject);

    // fill price
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

  public function findItem(string $category, bool $next = false): array|null
  {
    $items = $next ? ($this->next_invoice['items'] ?? []) : $this->items;
    return ProductItem::findItem($items, $category);
  }

  public function findPlanItem(bool $next = false): array|null
  {
    return $this->findItem(ProductItem::ITEM_CATEGORY_PLAN, $next);
  }

  public function findLicenseItem(bool $next = false): array|null
  {
    return $this->findItem(ProductItem::ITEM_CATEGORY_LICENSE, $next);
  }

  public function fillFromSubscriptionNext(Subscription $subscription)
  {
    $this->subtotal = $subscription->next_invoice['subtotal'];
    $this->total_tax = $subscription->next_invoice['total_tax'];
    $this->total_amount = $subscription->next_invoice['total_amount'];
    $this->payment_method_info = $subscription->payment_method_info;

    return $this;
  }

  public function setDisputeStatus(string $dispute_status, Carbon $time = null)
  {
    //
    // DISPUTE_STATUS_DISPUTED is a final state. However we still record the transition time of other status.
    //
    if (
      $this->dispute_status !== self::DISPUTE_STATUS_DISPUTED ||
      $dispute_status === self::DISPUTE_STATUS_DISPUTED
    ) {
      $this->dispute_status = $dispute_status;
    }

    $dispute_status_transitions = $this->dispute_status_transitions ?? [];
    $dispute_status_transitions[$dispute_status] = $time ?? now();
    $this->dispute_status_transitions = $dispute_status_transitions;

    return $this;
  }

  public function getDisputeStatus(): string
  {
    return $this->dispute_status;
  }

  public function getDisputeStatusTimestamp(string $dispute_status): Carbon|null
  {
    if (isset($this->dispute_status_transitions[$dispute_status])) {
      return Carbon::parse($this->dispute_status_transitions[$dispute_status]);
    }
    return null;
  }

  public function isCancellable(): bool
  {
    return ($this->period === 0 && $this->status === self::STATUS_PENDING);
  }

  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }
}
