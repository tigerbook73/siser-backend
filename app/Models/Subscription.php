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
  public const SUB_STATUS_CANCELLING          = 'cancelling';
  public const SUB_STATUS_NORMAL              = 'normal';
  public const SUB_STATUS_ORDER_PENDING       = 'order_pending'; // for STATUS_PENDING

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
    } else if ($this->plan_info['interval'] == Plan::INTERVAL_YEAR) {
      /** @var Plan @newPlan */
      $newPlan = Plan::public()
        ->where('interval', Plan::INTERVAL_MONTH)
        ->where('interval_count', 1)
        ->where('subscription_level', $this->plan_info['subscription_level'])
        ->where('product_name', $this->plan_info['product_name'])
        ->first();
      $next_invoice['plan_info'] = $newPlan->info($this->billing_info['address']['country']);
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
    $next_invoice['tax_rate'] = $this->tax_rate; // TODO: free-trial's tax rate is not correct, always 0
    $next_invoice['total_tax'] = round($next_invoice['price'] * $next_invoice['tax_rate'], 2);
    $next_invoice['total_amount'] = round($next_invoice['subtotal'] + $next_invoice['total_tax']);

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
}
