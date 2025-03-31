<?php

namespace App\Models;

use App\Models\Base\Subscription as BaseSubscription;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Subscription extends BaseSubscription
{
  use Notifiable;
  use TraitStatusTransition;

  // status
  public const STATUS_ACTIVE                  = 'active';
  public const STATUS_DRAFT                   = 'draft';
  public const STATUS_FAILED                  = 'failed';
  public const STATUS_STOPPED                 = 'stopped';

  // sub_status (when status is 'active')
  public const SUB_STATUS_CANCELLING          = 'cancelling';     // to be cancelled at the end of current period
  public const SUB_STATUS_NORMAL              = 'normal';         // default sub status for all status

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

    $billingInfo = ($user->billing_info ?? BillingInfo::createDefault($user))->info();
    $planInfo = $plan->info('US');

    $subscription = new Subscription(
      [
        'user_id'                   => $user->id,
        'plan_id'                   => $plan->id,
        'billing_info'              => $billingInfo->toArray(),
        'plan_info'                 => $planInfo->toArray(),
        'items'                     => [
          (new SubscriptionItem(
            name: $planInfo->name,
            currency: $planInfo->price->currency,
            price: $planInfo->price->price,
            discount: 0.0,
            tax: 0.0,
            amount: $planInfo->price->price,
            quantity: 1,
          ))->toArray()
        ],
        'currency'                  => $planInfo->price->currency,
        'price'                     => $planInfo->price->price,
        'subtotal'                  => $planInfo->price->price,
        'tax_rate'                  => 0.0,
        'total_tax'                 => 0.0,
        'total_amount'              => $planInfo->price->price,
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

  public function initFill(): self
  {
    $this->start_date                 = null;
    $this->end_date                   = null;
    $this->current_period             = 0;
    $this->current_period_start_date  = null;
    $this->current_period_end_date    = null;
    $this->next_invoice_date          = null;
    $this->next_reminder_date         = null;
    $this->sub_status                 = Subscription::SUB_STATUS_NORMAL;
    $this->stop_reason                = '';
    $this->setNextInvoice(null);
    $this->setStatus(Subscription::STATUS_DRAFT);
    return $this;
  }

  /**
   * TODO: for test only now, to be deleted
   */
  public function fillBillingInfo(BillingInfo $billingInfo): self
  {
    $country = Country::findByCode($billingInfo->address()->country);
    $this->user_id      = $billingInfo->user_id;
    $this->currency     = $country->currency;
    $this->setBillingInfo($billingInfo->info());
    return $this;
  }

  /**
   * TODO: for test only now, to be deleted
   */
  public function fillPlanAndCoupon(Plan $plan, ?Coupon $coupon = null, ?LicensePackage $licensePackage = null, int $licenseQuantity = 0, float $taxRate = 0.0): self
  {
    $planInfo     = $plan->info($this->getBillingInfo()->address->country);
    $couponInfo   = $coupon?->info($this->start_date);
    $licensePackageInfo = $licensePackage?->info($licenseQuantity);

    $this->plan_id              = $planInfo->id;
    $this->subscription_level   = $planInfo->subscription_level;
    $this->coupon_id            = $couponInfo?->id;
    $this->setPlanInfo($planInfo);
    $this->setCouponInfo($couponInfo);
    $this->setLicensePackageInfo($licensePackageInfo);

    $this->setItems(
      SubscriptionItem::buildItemsForTest(
        $planInfo,
        $couponInfo,
        $licensePackageInfo,
        taxRate: $taxRate
      )
    );

    // set prices based on items
    $item = $this->getItems()[0];
    $this->price = $item->price;
    $this->subtotal = $item->price;
    $this->total_tax = $item->tax;
    $this->total_amount = $item->amount;
    return $this;
  }

  public function stop(string $status, string $stopReason = '')
  {
    $prevStatus = $this->status;

    $this->end_date = $this->start_date ? now() : null;
    $this->next_invoice_date = null;
    $this->next_reminder_date = null;
    $this->setNextInvoice(null);

    $this->setStatus($status);
    $this->stop_reason = $stopReason;
    $this->sub_status = Subscription::SUB_STATUS_NORMAL;
    $this->save();

    if ($prevStatus === Subscription::STATUS_ACTIVE && $this->subscription_level > 1) {
      SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_STOPPED, $this);
    }
  }

  /**
   * billing info
   */
  public function getBillingInfo(): BillingInformation
  {
    return BillingInformation::from($this->billing_info);
  }

  public function setBillingInfo(BillingInformation $billingInfo): self
  {
    $this->billing_info = $billingInfo->toArray();
    return $this;
  }

  /**
   * payment method info
   */
  public function getPaymentMethodInfo(): PaymentMethodInfo
  {
    return PaymentMethodInfo::from($this->payment_method_info);
  }

  public function setPaymentMethodInfo(PaymentMethodInfo $paymentMethodInfo): self
  {
    $this->payment_method_info = $paymentMethodInfo->toArray();
    return $this;
  }

  /**
   * plan info
   */
  public function getPlanInfo(): PlanInfo
  {
    return PlanInfo::from($this->plan_info);
  }

  public function setPlanInfo(PlanInfo $planInfo): self
  {
    $this->plan_info = $planInfo->toArray();
    return $this;
  }


  /**
   * license package info
   */
  public function hasLicensePackageInfo(): bool
  {
    return $this->license_package_info !== null;
  }

  public function getLicensePackageInfo(): ?LicensePackageInfo
  {
    return $this->license_package_info ? LicensePackageInfo::from($this->license_package_info) : null;
  }

  public function setLicensePackageInfo(?LicensePackageInfo $licensePackageInfo): self
  {
    $this->license_package_info = $licensePackageInfo?->toArray();
    return $this;
  }

  /**
   * items
   */

  /**
   * @return SubscriptionItem[]
   */
  public function getItems(): array
  {
    return SubscriptionItem::itemsFrom($this->items);
  }

  /**
   * @param SubscriptionItem[] $items
   * @return self
   */
  public function setItems(array $items): self
  {
    $this->items = array_map(fn($item) => $item->toArray(), $items);
    return $this;
  }

  /**
   * coupon
   */

  public function hasCouponInfo(): bool
  {
    return $this->coupon_info !== null;
  }

  public function getCouponInfo(): ?CouponInfo
  {
    return $this->coupon_info ? CouponInfo::from($this->coupon_info) : null;
  }

  public function setCouponInfo(?CouponInfo $couponInfo): self
  {
    $this->coupon_info = $couponInfo?->toArray();
    return $this;
  }

  /**
   * next invoice
   */

  public function hasNextInvoice(): bool
  {
    return $this->next_invoice !== null;
  }

  public function getNextInvoice(): ?SubscriptionNextInvoice
  {
    return $this->next_invoice ? SubscriptionNextInvoice::from($this->next_invoice) : null;
  }

  public function setNextInvoice(?SubscriptionNextInvoice $nextInvoice): self
  {
    $this->next_invoice = $nextInvoice?->toArray();
    return $this;
  }

  /**
   * notification related
   */

  public function routeNotificationForMail($notification)
  {
    $billingInfo = $this->getBillingInfo();
    return [
      $billingInfo->email => $billingInfo->first_name . ' ' . $billingInfo->last_name
    ];
  }

  public function sendNotification(string $type, ?Invoice $invoice = null, array $context = [])
  {
    $context['subscription'] = $this;
    $context['invoice'] = $invoice;
    $this->notify(new SubscriptionNotification($type, $context));

    Log::Info("NOTIF_LOG: {$type} sent to user: {$this->user_id}, email: {$context['subscription']->getBillingInfo()->email} for subscription: {$this->id}");
  }

  public function isFreeTrial(): bool
  {
    return $this->getCouponInfo()?->discount_type === Coupon::DISCOUNT_TYPE_FREE_TRIAL;
  }

  public function isPercentage(): bool
  {
    return $this->getCouponInfo()?->discount_type === Coupon::DISCOUNT_TYPE_PERCENTAGE;
  }

  public function isFixedTermPercentage(): bool
  {
    $couponInfo = $this->getCouponInfo();
    if (!$couponInfo) {
      return false;
    }
    return $couponInfo->discount_type === Coupon::DISCOUNT_TYPE_PERCENTAGE &&
      $couponInfo->interval_count != 0 &&
      $couponInfo->interval != Coupon::INTERVAL_LONGTERM;
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
    return $this->subscription_level > 1;
  }

  /**
   * meta
   */
  public function getMeta(): SubscriptionMeta
  {
    return SubscriptionMeta::from($this->meta ?? []);
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
