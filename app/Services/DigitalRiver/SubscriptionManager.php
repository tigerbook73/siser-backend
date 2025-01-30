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
  public function cancelSubscription(Subscription $subscription, bool $immediate): Subscription;
  public function stopSubscription(Subscription $subscription, string $reason): Subscription;
  public function failSubscription(Subscription $subscription, string $reason): Subscription;

  /**
   * Invoice
   */
  // public function createNewLicensePackageInvoice(Subscription $subscription, LicensePackage $licensePackage, int $licenseCount): Invoice;
  // public function createIncreaseLicenseInvoice(Subscription $subscription, int $licenseCount);
  // public function cancelLicensePackage(Subscription $subscription, bool $immediate = false): Subscription;
  // public function decreaseLicenseNumber(Subscription $subscription, int $licenseCount, bool $immediate = false): Subscription;

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
