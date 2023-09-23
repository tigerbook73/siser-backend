<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;

interface SubscriptionManager
{
  /**
   * Subscription
   */
  public function createSubscription(User $user, Plan $plan, Coupon|null $coupon = null, TaxId|null $taxId = null): Subscription;
  public function deleteSubscription(Subscription $subscription): bool;
  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string|null $terms): Subscription;
  public function cancelSubscription(Subscription $subscription, bool $refund = false, bool $immediate = false): Subscription;

  /**
   * Invoice
   */
  public function cancelOrder(Invoice $invoice): Invoice;

  /**
   * TaxId
   */
  public function createTaxId(User $user, string $type, string $value): TaxId;
  public function deleteTaxId(TaxId $taxId);

  /**
   * Customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo);

  /**
   * Payment
   */
  public function updatePaymentMethod(User $user, string $sourceId): PaymentMethod;

  /**
   * Refund
   */
  public function createRefund(Invoice $invoice, float $amount = 0, string $reason = null): Refund;

  /**
   * Default webhook
   */
  public function updateDefaultWebhook(bool $enable);

  /**
   * Webhook event handler
   */
  public function webhookHandler(array $event): \Illuminate\Http\JsonResponse;
}
