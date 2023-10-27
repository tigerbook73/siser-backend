<?php

namespace App\Models;

use App\Models\Base\Subscription as BaseSubscription;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
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
    'tax_id_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'plan_info'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'coupon_info'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'payment_method_info'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'currency'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'price'                     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subtotal'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
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
    'renewal_info'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'                        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'stop_reason'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status_transitions'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'sub_status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  static protected $withAttrs = ['plan', 'coupon'];

  static public function createBasicMachineSubscription(User $user): Subscription
  {
    /** @var Plan $plan */
    $plan = Plan::find(config('siser.plan.default_machine_plan'));

    $subscription = new Subscription(
      [
        'user_id'                   => $user->id,
        'plan_id'                   => $plan->id,
        'billing_info'              => ($user->billing_info ?? BillingInfo::createDefault($user))->info(),
        'plan_info'                 => $plan->info('US'),
        'currency'                  => 'USD',
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
    $subscription->setStatus(Subscription::STATUS_DRAFT);
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
    return $this->getDrAttr(self::DR_SUBSCRIPTION_ID);
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

  public function fillPlanAndCoupon(Plan $plan, Coupon $coupon = null): self
  {
    $plan_info    = $plan->info($this->billing_info['address']['country']);
    $coupon_info  = $coupon?->info();

    $this->plan_id            = $plan_info['id'];
    $this->plan_info          = $plan_info;
    $this->subscription_level = $plan_info['subscription_level'];
    $this->coupon_id          = $coupon_info['id'] ?? null;
    $this->coupon_info        = $coupon_info;
    $this->price              = self::calcPlanPrice($plan_info, $coupon_info);
    return $this;
  }

  public function fillTaxId(TaxId $taxId = null): self
  {
    $this->tax_id_info = $taxId?->info();
    return $this;

    // TODO: fill tax id from dr object
  }

  public function fillPaymentMethod(PaymentMethod $paymentMethod): self
  {
    $this->payment_method_info = $paymentMethod->info();
    $this->setDrSourceId($paymentMethod->getDrSourceId());
    return $this;
  }

  public function fillAmountFromDrObject(DrCheckout|DrOrder|DrInvoice $drObject): self
  {
    // Note: DrCheckout, DrOrder and DrInvoice has same following memeber functions
    $this->subtotal = $drObject->getSubtotal();
    $this->tax_rate = $drObject->getItems()[0]->getTax()->getRate();
    $this->total_tax = $drObject->getTotalTax();
    $this->total_amount = $drObject->getTotalAmount();
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
    $this->price                     = $next_invoice['price'];
    $this->subtotal                  = $next_invoice['subtotal'];
    $this->tax_rate                  = $next_invoice['tax_rate'];
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

    if ($this->isFreeTrial()) {
      $next_invoice['coupon_info'] = null;
    } else if ($this->isFixedTermPercentage()) {
      if ($next_invoice['current_period'] * $this->plan_info['interval_count'] > $this->coupon_info['interval_count']) {
        $next_invoice['coupon_info'] = null;
      }
    }

    $next_invoice['current_period_start_date'] = $this->current_period_end_date->addSecond()->toDateTimeString();
    $next_invoice['current_period_end_date'] = $this->current_period_end_date->add(
      $next_invoice['plan_info']['interval'],
      $next_invoice['plan_info']['interval_count']
    )->toDateTimeString();

    $next_invoice['price'] = self::calcPlanPrice($next_invoice['plan_info'], $next_invoice['coupon_info']);
    $next_invoice['subtotal'] = $next_invoice['price'];
    $next_invoice['tax_rate'] = $this->tax_rate;
    $next_invoice['total_tax'] = round($next_invoice['price'] * $next_invoice['tax_rate'], 2);
    $next_invoice['total_amount'] = round($next_invoice['subtotal'] + $next_invoice['total_tax'], 2);

    $this->next_invoice = [
      'current_period'            => $next_invoice['current_period'],
      'current_period_start_date' => $next_invoice['current_period_start_date'],
      'current_period_end_date'   => $next_invoice['current_period_end_date'],
      'plan_info'                 => $next_invoice['plan_info'],
      'coupon_info'               => $next_invoice['coupon_info'],
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
    $next_invoice['subtotal']       = $drObject->getSubtotal();
    $next_invoice['tax_rate']       = $drObject->getItems()[0]->getTax()->getRate();
    $next_invoice['total_tax']      = $drObject->getTotalTax();
    $next_invoice['total_amount']   = $drObject->getTotalAmount();
    $this->next_invoice = $next_invoice;
    return $this;
  }

  public function isNextPlanDifferent(): bool
  {
    return $this->plan_info['id'] !== $this->next_invoice['plan_info']['id'] ||
      ($this->coupon_info['id'] ?? null) !== ($this->next_invoice['coupon_info']['id'] ?? null);
  }

  public function stop(string $status, string $stopReason = '', string $subStatus = Subscription::SUB_STATUS_NORMAL)
  {
    $this->end_date = $this->start_date ? now() : null;
    $this->next_invoice_date = null;
    $this->next_reminder_date = null;
    $this->next_invoice = null;
    $this->active_invoice_id = null;

    $this->setStatus($status);
    $this->stop_reason = $stopReason;
    $this->sub_status = $subStatus;
    $this->save();

    $this->cancelPendingOrActiveRenewal();
  }

  public function getActiveInvoice(): Invoice|null
  {
    return $this->active_invoice_id ? $this->invoices()->find($this->active_invoice_id) : null;
  }

  public function getCurrentPeriodInvoice(): Invoice|null
  {
    return $this->invoices()->where('period', $this->current_period)->first();
  }

  public function getInvoiceByOrderId(string $orderId): Invoice|null
  {
    return $this->invoices()->where('dr_order_id', $orderId)->first();
  }

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
    return ($this->coupon_info['discount_type'] ?? null) === Coupon::DISCOUNT_TYPE_PERCENTAGE && $this->coupon_info['interval_count'] != 0;
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
    if (!$coupon_info) {
      return $plan_info['price']['price'];
    }

    if ($coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      return 0;
    }

    return round($plan_info['price']['price'] * (100 - $coupon_info['percentage_off']) / 100, 2);
  }

  public function getPlanName(): string
  {
    return self::buildPlanName($this->plan_info, $this->coupon_info);
  }

  public function isRenewalRequired(): bool
  {
    // must be active and not cancelling
    if ($this->status !== Subscription::STATUS_ACTIVE || $this->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      return false;
    }

    //  must have next invoice date
    if (!$this->next_invoice_date) {
      return false;
    }

    // must be annual plan & german
    if ($this->plan_info['interval'] !== Plan::INTERVAL_YEAR || $this->billing_info['address']['country'] !== 'DE') {
      return false;
    }

    // must not be free trial
    if ($this->isFreeTrial()) {
      return false;
    }

    return true;
  }

  public function isRenewalPendingOrActive(): bool
  {
    return $this->renewal_info &&
      ($this->renewal_info['status'] === SubscriptionRenewal::STATUS_PENDING ||
        $this->renewal_info['status'] === SubscriptionRenewal::STATUS_ACTIVE);
  }

  public function createRenewal(): SubscriptionRenewal|null
  {
    if (!$this->isRenewalRequired()) {
      return null;
    }

    /**
     *  Germany annual plan
     */
    $renewal = new SubscriptionRenewal();
    $renewal->user_id = $this->user_id;
    $renewal->subscription_id = $this->id;
    $renewal->period = $this->current_period + 1;

    /**
     *  renew date offsets (see config/dr.php for real offsets)
     *          |--------------------------------------------| next_invoice_date 
     *          |                 |            |<-- 2 day -->|
     *          |                 |<-- 4 days             -->|
     *          |<-- 30 days                              -->|
     *         start_at                       expire_at
     *         first_reminder_date 
     *                          final_reminder_date
     */
    $renewal->start_at = min($this->next_invoice_date->subDays(config('dr.renewal.start_offset')), $this->next_reminder_date);
    $renewal->expire_at = $this->next_invoice_date->subDays(config('dr.renewal.expire_offset'));
    $renewal->first_reminder_at = $this->next_invoice_date->subDays(config('dr.renewal.first_reminder_offset'));
    $renewal->final_reminder_at = $this->next_invoice_date->subDays(config('dr.renewal.final_reminder_offset'));

    $renewal->pending();
    $renewal->save();

    $this->renewal_info = $renewal->info();
    $this->save();
    return $renewal;
  }

  public function getOpenRenewal(): SubscriptionRenewal|null
  {
    return $this->subscription_renewals()
      ->where('period', $this->current_period + 1)
      ->whereIn('status', [SubscriptionRenewal::STATUS_PENDING, SubscriptionRenewal::STATUS_ACTIVE])
      ->first();
  }

  public function getActiveRenewal(): SubscriptionRenewal|null
  {
    return $this->subscription_renewals()
      ->where('period', $this->current_period + 1)
      ->where('status', SubscriptionRenewal::STATUS_ACTIVE)
      ->first();
  }

  public function activatePendingRenewal(): SubscriptionRenewal|null
  {
    $renewal = $this->getOpenRenewal();
    if ($renewal && $renewal->status === SubscriptionRenewal::STATUS_PENDING) {
      $renewal->activate();
      $renewal->save();

      $this->renewal_info = $renewal->info();
      $this->save();
    }
    return $renewal;
  }

  public function updateActiveRenewalSubstatus(string $substatus)
  {
    $renewal = $this->getActiveRenewal();
    if ($renewal) {
      $renewal->setSubStatus($substatus);
      $renewal->save();

      $this->renewal_info = $renewal->info();
      $this->save();
    }
    return $renewal;
  }

  public function cancelPendingOrActiveRenewal(): SubscriptionRenewal|null
  {
    $renewal = $this->getOpenRenewal();
    if (!$renewal) {
      return null;
    }

    if ($renewal->isPending()) {
      $renewal->delete();
      $this->renewal_info = null;
      $this->save();
    } else {
      // active
      $renewal->cancel();
      $renewal->save();

      $this->renewal_info = $renewal->info();
      $this->save();
    }
    return $renewal;
  }

  public function expireActiveRenewal(): SubscriptionRenewal|null
  {
    $renewal = $this->getActiveRenewal();
    if ($renewal) {
      $renewal->expire();
      $renewal->save();

      $this->renewal_info = $renewal->info();
      $this->save();
    }
    return $renewal;
  }

  public function completeActiveRenewal(): SubscriptionRenewal|null
  {
    $renewal = $this->getActiveRenewal();
    if ($renewal) {
      $renewal->complete();
      $renewal->save();

      $this->renewal_info = $renewal->info();
      $this->save();
    }
    return $renewal;
  }

  /**
   * Send notification if renewal is required
   */
  public function sendRenewNotification(): self|null
  {
    if (!$renewal = $this->getOpenRenewal()) {
      return null;
    }

    $now = now();

    if ($renewal->isPending()) {
      if ($renewal->start_at <= $now && $renewal->expire_at > $now) {
        $renewal->activate();
      }
    }

    if ($renewal->isActive()) {
      // try first notification
      if (
        $renewal->sub_status === SubscriptionRenewal::SUB_STATUS_READY &&
        ($renewal->first_reminder_at && $renewal->first_reminder_at <= $now) &&
        (!$renewal->final_reminder_at || $renewal->final_reminder_at > $now) &&
        $renewal->expire_at > $now
      ) {
        $this->sendNotification(SubscriptionNotification::NOTIF_RENEW_REQUIRED);

        $renewal->setSubStatus(SubscriptionRenewal::SUB_STATUS_FIRST_REMINDERED);
        $this->renewal_info = $renewal->info();
        $renewal->save();
        $this->save();
        return $this;
      }

      // try final notification
      if (
        ($renewal->sub_status === SubscriptionRenewal::SUB_STATUS_READY || $renewal->sub_status === SubscriptionRenewal::SUB_STATUS_FIRST_REMINDERED) &&
        ($renewal->final_reminder_at && $renewal->final_reminder_at->lte($now)) &&
        $renewal->expire_at->gt($now)
      ) {
        $this->sendNotification(SubscriptionNotification::NOTIF_RENEW_REQUIRED);

        $renewal->setSubStatus(SubscriptionRenewal::SUB_STATUS_FINAL_REMINDERED);
        $this->renewal_info = $renewal->info();
        $renewal->save();
        $this->save();
        return $this;
      }
    }

    return null;
  }
}
