<?php

namespace App\Models;

use App\Models\Base\Invoice as BaseInvoice;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Illuminate\Database\Eloquent\Collection;

class Invoice extends BaseInvoice
{
  use TraitStatusTransition {
    setStatus as protected traitSetStatus;
  }
  use TraitDrAttr;

  // type
  public const TYPE_NEW_SUBSCRIPTION    = 'new-subscription';
  public const TYPE_RENEW_SUBSCRIPTION  = 'renew-subscription';
  public const TYPE_UPDATE_SUBSCRIPTION = 'update-subscription';

  // status -- see invoice.md
  public const STATUS_INIT            = 'init';
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
  public const DR_CHECKOUT_ID       = 'checkout_id';
  public const DR_INVOICE_ID        = 'invoice_id';
  public const DR_ORDER_ID          = 'order_id';
  public const DR_INVOICE_ID_LIST   = 'invoice_id_list';
  public const DR_SESSION_ID        = 'checkout_payment_session_id';

  static protected $attributesOption = [
    'id'                          => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'                     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'                        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period'                      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_start_date'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'period_end_date'             => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_package_info'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'items'                       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'discount'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'                   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_refunded'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'available_to_refund_amount'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'invoice_date'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'pdf_file'                    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'credit_memos'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'extra_data'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_0_1],
    'status'                      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'sub_status'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dispute_status'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'dispute_status_transitions'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'meta'                        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): self
  {
    if (!in_array($type, [
      self::TYPE_NEW_SUBSCRIPTION,
      self::TYPE_RENEW_SUBSCRIPTION,
      self::TYPE_UPDATE_SUBSCRIPTION
    ])) {
      throw new \InvalidArgumentException("Invalid type: $type");
    }

    $this->type = $type;
    return $this;
  }

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

  public function getDrCheckoutId(): string|null
  {
    return $this->getDrAttr(self::DR_CHECKOUT_ID);
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

  public function getDrSessionId()
  {
    return $this->getDrAttr(self::DR_SESSION_ID);
  }

  public function setDrFileId(string $drFileId): self
  {
    return $this->setDrAttr(self::DR_FILE_ID, $drFileId);
  }

  public function setDrCheckoutId(string $drCheckoutId): self
  {
    return $this->setDrAttr(self::DR_CHECKOUT_ID, $drCheckoutId);
  }

  public function setDrInvoiceId(string $drInvoiceId): self
  {
    if ($this->dr_invoice_id == $drInvoiceId) {
      return $this;
    }

    $this->dr_invoice_id = $drInvoiceId;
    $this->setDrAttr(self::DR_INVOICE_ID, $drInvoiceId);

    $drInvoiceIdList = $this->getDrInvoiceIdList();
    $drInvoiceIdList[] = $drInvoiceId;
    $this->setDrAttr(self::DR_INVOICE_ID_LIST, $drInvoiceIdList);
    return $this;
  }

  public function setDrOrderId(string|null $drOrderId): self
  {
    $this->dr_order_id = $drOrderId;
    return $this->setDrAttr(self::DR_ORDER_ID, $drOrderId);
  }

  public function setDrSessionId(string $drSessionId): self
  {
    return $this->setDrAttr(self::DR_SESSION_ID, $drSessionId);
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

  public function fillBasic(Subscription $subscription): self
  {
    // static part
    $this->user_id                = $subscription->user_id;
    $this->subscription_id        = $subscription->id;
    $this->currency               = $subscription->currency;

    $this->billing_info           = $subscription->billing_info;

    // dynamic part
    $this->payment_method_info    = $subscription->payment_method_info;

    return $this;
  }

  public function fillFromDrObject(DrCheckout|DrOrder|DrInvoice $drObject): self
  {
    if ($drObject instanceof DrOrder) {
      $this->setDrOrderId($drObject->getId());
      $this->total_refunded = $drObject->getRefundedAmount() ?? 0;
      $this->available_to_refund_amount = in_array($drObject->getState(), [DrOrder::STATE_ACCEPTED, DrOrder::STATE_FULFILLED]) ?
        $drObject->getTotalAmount() : ($drObject->getAvailableToRefundAmount() ?? 0);
    } else if ($drObject instanceof DrInvoice) {
      $this->setDrInvoiceId($drObject->getId());
      $this->setDrOrderId($drObject->getOrderId());
    } else if ($drObject instanceof DrCheckout) {
      $this->setDrCheckoutId($drObject->getId());
      $this->setDrSessionId($drObject->getPayment()->getSession()->getId());
    }

    // Note: DrCheckout, DrOrder and DrInvoice has same following memeber functions

    // fill items
    $this->items = ProductItem::buildItemsFromDrObject($drObject);

    // fill price
    $this->subtotal = $drObject->getSubtotal();
    $this->total_tax = $drObject->getTotalTax();
    $this->total_amount = $drObject->getTotalAmount();

    // fill payment method
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

  public function fillFromSubscription(Subscription $subscription): self
  {
    $this->plan_info            = $subscription->plan_info;
    $this->coupon_info          = $subscription->coupon_info;
    $this->license_package_info = $subscription->license_package_info;
    $this->items                = $subscription->items;

    $this->period               = $subscription->current_period ?: 1;
    $this->period_start_date    = $subscription->current_period_start_date;
    $this->period_end_date      = $subscription->current_period_end_date;
    $this->invoice_date         = now();

    $this->subtotal             = $subscription->subtotal ?? $subscription->price;
    $this->total_tax            = $subscription->total_tax ?? 0.00;
    $this->total_amount         = $subscription->total_amount ?? $subscription->price;

    $this->available_to_refund_amount = 0;

    return $this;
  }

  public function fillFromSubscriptionNext(Subscription $subscription): self
  {
    $this->plan_info            = $subscription->next_invoice['plan_info'];
    $this->coupon_info          = $subscription->next_invoice['coupon_info'];
    $this->license_package_info = $subscription->next_invoice['license_package_info'];
    $this->items                = $subscription->next_invoice['items'];

    $this->period               = $subscription->next_invoice['current_period'];
    $this->period_start_date    = $subscription->next_invoice['current_period_start_date'];
    $this->period_end_date      = $subscription->next_invoice['current_period_end_date'];
    $this->invoice_date         = $subscription->next_invoice_date;

    $this->subtotal             = $subscription->next_invoice['subtotal'];
    $this->total_tax            = $subscription->next_invoice['total_tax'];
    $this->total_amount         = $subscription->next_invoice['total_amount'];

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

  /**
   * this function shall be called after fillFromDrObject()
   */
  public function fillRefundStatus(): self
  {
    // skip
    if (in_array($this->status, [
      self::STATUS_INIT,
      self::STATUS_PENDING,
      self::STATUS_PROCESSING,
      self::STATUS_CANCELLED,
      self::STATUS_FAILED,
    ])) {
      return $this;
    }

    // fully refunded
    if ($this->total_amount > 0 && $this->total_refunded >= $this->total_amount) {
      $this->setStatus(self::STATUS_REFUNDED);
      return $this;
    }

    /** @var Collection<int, Refund> $refunds */
    $refunds = $this->refunds()
      ->orderBy('created_at')
      ->get();

    // skip if no refunds
    if ($refunds->isEmpty()) {
      return $this;
    }

    //
    // completed and pending refund are not available to refund
    // TODO: we rely on DrOrder to provide available_to_refund_amount
    //
    // $this->available_to_refund_amount = round(
    //   $this->total_amount - $refunds->reduce(
    //     fn($carry, $refund) => in_array($refund->status, [Refund::STATUS_COMPLETED, Refund::STATUS_PENDING]) ?
    //       $carry + $refund->amount :
    //       $carry
    //   ),
    //   2
    // );

    // to refunding if there is pending refund
    if ($refunds->first(fn($refund) => $refund->status === Refund::STATUS_PENDING)) {
      return $this->setStatus(self::STATUS_REFUNDING);
    }

    // to failed if lastest refund is failed
    if ($refunds->last()->status === Refund::STATUS_FAILED) {
      return $this->setStatus(self::STATUS_REFUND_FAILED);
    }

    // to partly refunded if there is completed refund
    if ($refunds->first(fn($refund) => $refund->status === Refund::STATUS_COMPLETED)) {
      return $this->setStatus(self::STATUS_PARTLY_REFUNDED);
    }

    // shall not go here
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
    return $this->isCustomerInitiatedOrder() && $this->isPending();
  }

  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }

  public function isProcessing(): bool
  {
    return $this->status === self::STATUS_PROCESSING;
  }

  public function isNewSubscriptionOrder(): bool
  {
    return $this->type === self::TYPE_NEW_SUBSCRIPTION;
  }

  public function isRenewSubscritpionOrder(): bool
  {
    return $this->type === self::TYPE_RENEW_SUBSCRIPTION;
  }

  public function isSubscriptionOrder(): bool
  {
    return $this->type === self::TYPE_NEW_SUBSCRIPTION || $this->type === self::TYPE_RENEW_SUBSCRIPTION;
  }

  public function isCustomerInitiatedOrder(): bool
  {
    return $this->type === self::TYPE_NEW_SUBSCRIPTION;
  }

  /**
   * meta
   */
  public function getMeta(): InvoiceMeta
  {
    return InvoiceMeta::from($this->meta);
  }

  public function setMeta(InvoiceMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleSubscriptionId(?string $paddleSubscriptionId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->subscription_id = $paddleSubscriptionId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTransactionId(?string $paddleTransactionId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->transaction_id = $paddleTransactionId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleCustomerId(?string $paddleCustomerId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->customer_id = $paddleCustomerId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleDiscountId(?string $paddleDiscountId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->discount_id = $paddleDiscountId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTimestamp(?string $paddleTimestamp): self
  {
    $meta = $this->getMeta();
    $meta->paddle->paddle_timestamp = $paddleTimestamp;
    return $this->setMeta($meta);
  }
}
