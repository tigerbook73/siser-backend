<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

interface SubscriptionManager
{
  /**
   * Subscription
   */
  public function createSubscription(User $user, Plan $plan, Coupon|null $coupon): Subscription;
  public function updateSubscription(Subscription $subscription): Subscription;
  public function deleteSubscription(Subscription $subscription): bool;
  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string|null $terms): Subscription;
  public function cancelSubscription(Subscription $subscription): Subscription;

  /**
   * Customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo);

  /**
   * Payment
   */
  public function updatePaymentMethod(User $user, string $sourceId): PaymentMethod;

  /**
   * Default webhook
   */
  public function updateDefaultWebhook(bool $enable);

  /**
   * Webhook event handler
   * @return bool true: event processed, false: event not processed
   */
  public function webhookHandler(array $event): bool;
}
