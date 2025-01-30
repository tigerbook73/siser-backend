<?php

namespace App\Models;

use App\Models\Base\Subscription as BaseSubscription;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Subscription extends BaseSubscription
{
  use Notifiable;
  use TraitStatusTransition, TraitDrAttr;

  // status
  public const STATUS_ACTIVE                  = 'active';
  public const STATUS_DRAFT                   = 'draft';
  public const STATUS_FAILED                  = 'failed';
  public const STATUS_PENDING                 = 'pending';
  public const STATUS_STOPPED                 = 'stopped';

  // sub_status (when status is 'active')
  public const SUB_STATUS_CANCELLING          = 'cancelling';     // to be cancelled at the end of current period
  public const SUB_STATUS_NORMAL              = 'normal';         // default sub status for all status
  public const SUB_STATUS_ORDER_PENDING       = 'order_pending';  // for STATUS_PENDING

  // dr attributes
  public const DR_CUSTOMER_ID       = 'customer_id';
  public const DR_CHECKOUT_ID       = 'checkout_id';
  public const DR_ORDER_ID          = 'order_id';
  public const DR_SESSION_ID        = 'checkout_payment_session_id';
  public const DR_SOURCE_ID         = 'source_id';
  public const DR_SUBSCRIPTION_ID   = 'subscription_id';

  static protected $attributesOption = [
    'id'                        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_id'                   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_id'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'billing_info'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_package_info'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'items'                     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'price'                     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'discount'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_rate'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_tax'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_amount'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'start_date'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'end_date'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_start_date' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'current_period_end_date'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_invoice_date'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_reminder_date'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'next_invoice'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'stop_reason'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'sub_status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'meta'                      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  static protected $withAttrs = ['plan', 'coupon'];

  static public function createBasicMachineSubscription(User $user): Subscription
  {
    /** @var Plan $plan */
    $plan = Plan::find(config('siser.plan.default_machine_plan'));

    $billing_info = ($user->billing_info ?? BillingInfo::createDefault($user))->info();
    $plan_info = $plan->info($billing_info['address']['country']);

    $subscription = new Subscription(
      [
        'user_id'                   => $user->id,
        'plan_id'                   => $plan->id,
        'billing_info'              => $billing_info,
        'plan_info'                 => $plan_info,
        'items'                     => ProductItem::buildItems($plan_info),
        'currency'                  => $plan_info['price']['currency'],
        'price'                     => 0.0,
        'subtotal'                  => 0.0,
        'tax_rate'                  => 0.0,
        'total_tax'                 => 0.0,
        'total_amount'              => 0.0,
        'subscription_level'        => 1,
        'current_period'            => 1,
        'start_date'                => new Carbon(),
        'current_period_start_date' => new Carbon(),
        'status'                    => Subscription::STATUS_ACTIVE,
        'sub_status'                => Subscription::SUB_STATUS_NORMAL,
      ]
    );
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->save();
    return $subscription;
  }

  public function getDrCustomerId(): string|null
  {
    return $this->getDrAttr(self::DR_CUSTOMER_ID);
  }

  public function getDrCheckoutId(): string|null
  {
    return $this->getDrAttr(self::DR_CHECKOUT_ID);
  }

  public function getDrOrderId(): string|null
  {
    return $this->getDrAttr(self::DR_ORDER_ID);
  }

  public function getDrSessionId(): string|null
  {
    return $this->getDrAttr(self::DR_SESSION_ID);
  }

  public function getDrSourceId(): string|null
  {
    return $this->getDrAttr(self::DR_SOURCE_ID);
  }

  public function getDrSubscriptionId(): string|null
  {
    return $this->dr_subscription_id;
  }

  public function setDrCustomerId(string $customer_id): self
  {
    return $this->setDrAttr(self::DR_CHECKOUT_ID, $customer_id);
  }

  public function setDrCheckoutId(string $checkout_id): self
  {
    return $this->setDrAttr(self::DR_CHECKOUT_ID, $checkout_id);
  }

  public function setDrOrderId(string $order_id): self
  {
    return $this->setDrAttr(self::DR_ORDER_ID, $order_id);
  }

  public function setDrSessionId(string $session_id): self
  {
    return $this->setDrAttr(self::DR_SESSION_ID, $session_id);
  }

  public function setDrSourceId(string $source_id): self
  {
    return $this->setDrAttr(self::DR_SOURCE_ID, $source_id);
  }

  public function setDrSubscriptionId(string $subscription_id): self
  {
    $this->dr_subscription_id = $subscription_id;
    return $this->setDrAttr(self::DR_SUBSCRIPTION_ID, $subscription_id);
  }

  static public function findByDrSubscriptionId(string $drSubscriptionId): Subscription|null
  {
    return self::where('dr_subscription_id', $drSubscriptionId)->first();
  }

  public function initFill(): self
  {
    $this->start_date                 = null;
    $this->end_date                   = null;
    $this->current_period             = 0;
    $this->current_period_start_date  = null;
    $this->current_period_end_date    = null;
    $this->next_invoice_date          = null;
    $this->next_reminder_date         = null;
    $this->next_invoice               = null;
    $this->sub_status                 = Subscription::SUB_STATUS_NORMAL;
    $this->stop_reason                = '';
    $this->setStatus(Subscription::STATUS_DRAFT);
    return $this;
  }

  public function fillBillingInfo(BillingInfo $billingInfo): self
  {
    $country = Country::findByCode($billingInfo->address['country']);
    $this->user_id      = $billingInfo->user_id;
    $this->billing_info = $billingInfo->info();
    $this->currency     = $country->currency;
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

  public function fillItemsAndPrice(array $items): self
  {
    $this->items        = $items;
    $this->price        = ProductItem::calcTotal($items, 'price');
    $this->subtotal     = $this->price;
    $this->total_tax    = ProductItem::calcTotal($items, 'tax');
    $this->total_amount = round($this->subtotal + $this->total_tax, 2);
    return $this;
  }

  public function fillNextInvoiceItemsAndPrice(array &$next_invoice, array $items): self
  {
    $next_invoice['items']        = $items;
    $next_invoice['price']        = ProductItem::calcTotal($items, 'price');
    $next_invoice['subtotal']     = $next_invoice['price'];
    $next_invoice['tax_rate']     = $this->tax_rate ?? 0;
    $next_invoice['total_tax']    = ProductItem::calcTotal($items, 'tax');
    $next_invoice['total_amount'] = round($next_invoice['subtotal'] + $next_invoice['total_tax'], 2);
    return $this;
  }

  public function fillPlanAndCoupon(Plan $plan, Coupon $coupon = null, LicensePackage $licensePackage = null, int $licenseQuantity = 0): self
  {
    $plan_info    = $plan->info($this->billing_info['address']['country']);
    $coupon_info  = $coupon?->info();
    $license_package_info = $licensePackage?->info($licenseQuantity);

    $this->plan_id              = $plan_info['id'];
    $this->plan_info            = $plan_info;
    $this->subscription_level   = $plan_info['subscription_level'];
    $this->coupon_id            = $coupon_info['id'] ?? null;
    $this->coupon_info          = $coupon_info;
    $this->license_package_info = $license_package_info;

    $this->fillItemsAndPrice(
      ProductItem::buildItems($plan_info, $coupon_info, $license_package_info, $this->tax_rate, $this->items)
    );
    return $this;
  }

  public function fillPaymentMethod(PaymentMethod $paymentMethod): self
  {
    $this->payment_method_info = $paymentMethod->info();
    $this->setDrSourceId($paymentMethod->getDrSourceId());
    return $this;
  }

  public function fillAmountFromDrObject(DrCheckout|DrOrder|DrInvoice $drObject): self
  {
    // Note: DrCheckout, DrOrder and DrInvoice has same memeber functions

    // fill items
    $this->items = ProductItem::buildItemsFromDrObject($drObject);

    // fill price
    $this->price        = $drObject->getSubtotal();
    $this->subtotal     = $drObject->getSubtotal();
    $this->total_tax    = $drObject->getTotalTax();
    $this->total_amount = $drObject->getTotalAmount();
    $this->tax_rate     = ($drObject->getSubtotal() != 0 && $drObject->getTotalTax() == 0) ?
      0 :
      $drObject->getItems()[0]->getTax()->getRate();
    return $this;
  }

  public function fillPeriodFromDrObject(DrSubscription $drSubscription): self
  {
    $this->start_date = $this->start_date ?? Carbon::parse($drSubscription->getCurrentPeriodStartDate());
    $this->current_period = $this->current_period ?: 1;
    $this->current_period_start_date = Carbon::parse($drSubscription->getCurrentPeriodStartDate());
    $this->current_period_end_date = Carbon::parse($drSubscription->getCurrentPeriodEndDate());
    $this->next_invoice_date = Carbon::parse($drSubscription->getNextInvoiceDate());
    $this->next_reminder_date = Carbon::parse($drSubscription->getNextReminderDate());
    return $this;
  }

  public function moveToNext(): self
  {
    $next_invoice = $this->next_invoice;

    $this->current_period            = $next_invoice['current_period'];
    $this->current_period_start_date = $next_invoice['current_period_start_date'];
    $this->current_period_end_date   = $next_invoice['current_period_end_date'];
    $this->coupon_info               = $next_invoice['coupon_info'];
    $this->license_package_info      = $next_invoice['license_package_info'];
    $this->items                     = $next_invoice['items'];
    $this->price                     = $next_invoice['price'];
    $this->subtotal                  = $next_invoice['subtotal'];
    $this->tax_rate                  = $next_invoice['tax_rate'] ?? 0;
    $this->total_tax                 = $next_invoice['total_tax'];
    $this->total_amount              = $next_invoice['total_amount'];

    return $this;
  }

  public function fillNextInvoice(): self
  {
    $next_invoice['current_period'] = ($this->current_period ?: 1) + 1;

    // scenarios:
    // 1. free-trial: remove coupon, keep plan
    // 2. annual plan: to standard monthly plan, remove coupon
    // 3. percentage monthly plan (short term): if coupon expire, remove coupon, keep plan
    // 4. others, no change

    // others
    $next_invoice['plan_info'] = $this->plan_info;
    $next_invoice['coupon_info'] = $this->coupon_info;
    $next_invoice['license_package_info'] = $this->license_package_info;

    if ($this->isFreeTrial()) {
      $next_invoice['coupon_info'] = null;
    } else if ($this->isFixedTermPercentage()) {
      if ($next_invoice['current_period'] * $this->plan_info['interval_count'] > $this->coupon_info['interval_count']) {
        $next_invoice['coupon_info'] = null;
      }
    }

    // items & prices
    $this->fillNextInvoiceItemsAndPrice(
      $next_invoice,
      ProductItem::buildItems(
        $next_invoice['plan_info'],
        $next_invoice['coupon_info'],
        $next_invoice['license_package_info'],
        $this->tax_rate,
        $this->items
      )
    );

    $next_invoice['current_period_start_date'] = $this->current_period_end_date->addSecond()->toDateTimeString();
    $next_invoice['current_period_end_date'] = $this->current_period_end_date->add(
      $next_invoice['plan_info']['interval'],
      $next_invoice['plan_info']['interval_count']
    )->toDateTimeString();

    $this->next_invoice = [
      'current_period'            => $next_invoice['current_period'],
      'current_period_start_date' => $next_invoice['current_period_start_date'],
      'current_period_end_date'   => $next_invoice['current_period_end_date'],
      'plan_info'                 => $next_invoice['plan_info'],
      'coupon_info'               => $next_invoice['coupon_info'],
      'license_package_info'      => $next_invoice['license_package_info'],
      'items'                     => $next_invoice['items'],
      'price'                     => $next_invoice['price'],
      'subtotal'                  => $next_invoice['subtotal'],
      'tax_rate'                  => $next_invoice['tax_rate'],
      'total_tax'                 => $next_invoice['total_tax'],
      'total_amount'              => $next_invoice['total_amount'],
    ];
    return $this;
  }

  public function fillNextInvoiceAmountFromDrObject(DrOrder|DrInvoice $drObject): self
  {
    // Note: DrCheckout, DrOrder and DrInvoice has same following memeber functions
    $next_invoice = $this->next_invoice;

    // fill items
    $next_invoice['items'] = ProductItem::buildItemsFromDrObject($drObject);

    // fill price
    $next_invoice['subtotal']       = $drObject->getSubtotal();
    $next_invoice['total_tax']      = $drObject->getTotalTax();
    $next_invoice['total_amount']   = $drObject->getTotalAmount();
    $next_invoice['tax_rate']       = ($drObject->getSubtotal() != 0 && $drObject->getTotalTax() == 0) ?
      0 :
      $drObject->getItems()[0]->getTax()->getRate();


    $this->next_invoice = $next_invoice;
    return $this;
  }

  public function isNextPlanDifferent(): bool
  {
    if ($this->plan_info['id'] !== $this->next_invoice['plan_info']['id']) {
      return true;
    }

    if (($this->coupon_info['id'] ?? null) !== ($this->next_invoice['coupon_info']['id'] ?? null)) {
      return true;
    }

    if (($this->license_package_info['quantity'] ?? null) !== ($this->next_invoice['license_package_info']['quantity'] ?? null)) {
      return true;
    }
    return false;
  }

  public function stop(string $status, string $stopReason = '')
  {
    $prevStatus = $this->status;

    $this->end_date = $this->start_date ? now() : null;
    $this->next_invoice_date = null;
    $this->next_reminder_date = null;
    $this->next_invoice = null;

    $this->setStatus($status);
    $this->stop_reason = $stopReason;
    $this->sub_status = Subscription::SUB_STATUS_NORMAL;
    $this->save();

    if ($prevStatus === Subscription::STATUS_ACTIVE && $this->subscription_level > 1) {
      SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_STOPPED, $this);
    }
  }

  /**
   * invoice related
   */

  public function getCurrentPeriodInvoice(): Invoice|null
  {
    return $this->invoices()
      ->whereIn('type', [Invoice::TYPE_NEW_SUBSCRIPTION, Invoice::TYPE_RENEW_SUBSCRIPTION])
      ->where('period', $this->current_period)
      ->first();
  }

  public function getInvoiceByOrderId(string $orderId): Invoice|null
  {
    return $this->invoices()->where('dr_order_id', $orderId)->first();
  }

  public function getNewSubscriptionInvoice(): Invoice
  {
    return $this->invoices()
      ->where('type', Invoice::TYPE_NEW_SUBSCRIPTION)
      ->first();
  }

  public function getRenewingInvoice(): Invoice|null
  {
    return $this->invoices()
      ->where('type', Invoice::TYPE_RENEW_SUBSCRIPTION)
      ->whereIn('status', [Invoice::STATUS_INIT, Invoice::STATUS_PENDING])
      ->first();
  }

  public function hasPendingInvoice(): bool
  {
    return $this->invoices()
      ->where('status', Invoice::STATUS_PENDING)
      ->exists();
  }


  /**
   * notification related
   */

  public function routeNotificationForMail($notification)
  {
    return [
      $this->billing_info['email'] => $this->billing_info['first_name'] . ' ' . $this->billing_info['last_name']
    ];
  }

  public function sendNotification(string $type, Invoice $invoice = null, array $context = [])
  {
    $context['subscription'] = $this;
    $context['invoice'] = $invoice;
    $this->notify(new SubscriptionNotification($type, $context));

    Log::Info("NOTIF_LOG: {$type} sent to user: {$this->user_id}, email: {$this->billing_info['email']} for subscription: {$this->id}");
  }

  public function isFreeTrial(): bool
  {
    return ($this->coupon_info['discount_type'] ?? null) === Coupon::DISCOUNT_TYPE_FREE_TRIAL;
  }

  public function isPercentage(): bool
  {
    return ($this->coupon_info['discount_type'] ?? null) === Coupon::DISCOUNT_TYPE_PERCENTAGE;
  }

  public function isFixedTermPercentage(): bool
  {
    return ($this->coupon_info['discount_type'] ?? null) === Coupon::DISCOUNT_TYPE_PERCENTAGE &&
      $this->coupon_info['interval_count'] != 0 &&
      $this->coupon_info['interval'] != Coupon::INTERVAL_LONGTERM;
  }

  static public function buildPlanName(array $plan_info, array|null $coupon_info)
  {
    // free trial
    if ($coupon_info && $coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      return $coupon_info['name'];
    }

    // percentage off
    if ($coupon_info && $coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
      return "{$plan_info['name']} ({$coupon_info['name']})";
    }

    // standard plan
    return $plan_info['name'];
  }

  static public function calcPlanPrice(array $plan_info, array|null $coupon_info): float
  {
    $price = $plan_info['price']['price'];

    if (!$coupon_info) {
      return $price;
    }

    if ($coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      return 0;
    }

    return round($price * (100 - $coupon_info['percentage_off']) / 100, 2);
  }

  public function getPlanName(): string
  {
    return self::buildPlanName($this->plan_info, $this->coupon_info);
  }

  public function getNextInvoiceCollectionEndDate(): Carbon|null
  {
    /** @var SubscriptionPlan $subscriptionPlan */
    $subscriptionPlan = SubscriptionPlan::findByTypeAndIterval(
      SubscriptionPlan::TYPE_STANDARD,
      $this->plan_info['interval'],
      $this->plan_info['interval_count']
    );

    return $this->next_invoice_date?->addDays($subscriptionPlan->collection_period_days);
  }


  /**
   * status related
   */

  public function isActive()
  {
    return $this->status === Subscription::STATUS_ACTIVE;
  }

  public function isCancelling()
  {
    return $this->status === Subscription::STATUS_ACTIVE &&
      $this->sub_status === Subscription::SUB_STATUS_CANCELLING;
  }

  public function isPaid()
  {
    return $this->subscription_level > 2;
  }

  /**
   * meta
   */
  public function getMeta(): SubscriptionMeta
  {
    return SubscriptionMeta::from($this->meta);
  }

  public function setMeta(SubscriptionMeta $meta): self
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

  public function setMetaPaddleCustomerId(?string $paddleCustomerId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->customer_id = $paddleCustomerId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleProductId(?string $paddleProductId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->product_id = $paddleProductId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddlePriceId(?string $paddlePriceId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->price_id = $paddlePriceId;
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
