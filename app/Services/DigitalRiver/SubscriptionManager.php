<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicensePackage;
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
  public function createSubscription(User $user, Plan $plan, Coupon $coupon = null, TaxId $taxId = null, LicensePackage $licensePackage = null, int $licenseQuantity = 0): Subscription;
  public function deleteSubscription(Subscription $subscription): bool;
  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string|null $terms): Subscription;
  public function cancelSubscription(Subscription $subscription, bool $immediate): Subscription;
  public function stopSubscription(Subscription $subscription, string $reason): Subscription;
  public function failSubscription(Subscription $subscription, string $reason): Subscription;

  /**
   * Invoice
   */
  public function cancelInvoice(Invoice $invoice): Invoice;
  public function createNewLicensePackageInvoice(Subscription $subscription, LicensePackage $licensePackage, int $licenseCount): Invoice;
  public function createIncreaseLicenseInvoice(Subscription $subscription, int $licenseCount);
  public function deleteInvoice(Invoice $invoice): bool;
  public function payLicensePackageInvoice(Invoice $invoice, string $drSourceId): Invoice;
  public function cancelLicensePackage(Subscription $subscription, bool $immediate = false): Subscription;
  public function decreaseLicenseNumber(Subscription $subscription, int $licenseCount, bool $immediate = false): Subscription;



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
  public function createRefund(Invoice $invoice, string $itemType, float $amount = 0, string $reason = null): Refund;

  /**
   * Default webhook
   */
  public function updateDefaultWebhook(bool $enable);

  /**
   * Webhook event handler
   */
  public function webhookHandler(array $event): \Illuminate\Http\JsonResponse;

  /**
   * Tax rate
   */
  public function retrieveTaxRate(User $user, TaxId|null $taxId = null): float;

  /**
   * Manully renew subscription
   */
  public function renewSubscription(Subscription $subscription): Subscription;

  /**
   * try fix subscription/invoice state because missing events
   */
  public function tryCompleteInvoice(Invoice $invoice): bool;
}
