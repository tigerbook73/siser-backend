<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;

class RefundRules
{
  static public function invoiceRefundable(Invoice $invoice): RefundableResult
  {
    // check 1: invoice must be paid and not fully refunded
    if (
      $invoice->status != Invoice::STATUS_COMPLETED &&
      $invoice->status != Invoice::STATUS_PROCESSING &&
      $invoice->status != Invoice::STATUS_PARTLY_REFUNDED &&
      $invoice->status != Invoice::STATUS_REFUND_FAILED &&
      $invoice->status != Invoice::STATUS_REFUNDING
    ) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason("invoice in {$invoice->status} can not be refunded.");
    }

    // check 2: invoice must not be free trial
    if ($invoice->total_amount == 0) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason('invoice with total_amount = 0 can not be refunded');
    }

    // check 3: invoice must not be in dispute
    if (
      $invoice->dispute_status == Invoice::DISPUTE_STATUS_DISPUTING ||
      $invoice->dispute_status == Invoice::DISPUTE_STATUS_DISPUTED
    ) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason('invoice is in disputing or disputed');
    }

    // update invoice from DR order
    self::updateInvoicesFromDrOrder($invoice);

    // check 4: invoice must not be fully refunded (amount)
    if ($invoice->available_to_refund_amount < 0.01) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason('invoice is already fully refunded');
    }

    // result
    return (new RefundableResult())
      ->setRefundable(true)
      ->appendInvoices($invoice);
  }

  static protected function isSubscriptionRefundedBefore(User $user, string $productName): bool
  {
    /**
     * subscription refund
     */
    $count = $user->refunds()
      ->whereIn('status', [Refund::STATUS_COMPLETED, Refund::STATUS_PENDING])
      ->whereHas(
        'invoice',
        fn($query) => $query
          ->whereIn('type', [Invoice::TYPE_NEW_SUBSCRIPTION, Invoice::TYPE_RENEW_SUBSCRIPTION])
          ->where(fn($query) => $query
            ->whereNull('plan_info->product_name')
            ->orWhere('plan_info->product_name', $productName))
      )
      ->count();

    return $count > 0;
  }

  static public function subscriptionRefundable(Subscription $subscription): RefundableResult
  {
    // check 1: subscription must be active
    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason("subscription in {$subscription->status} can not be refunded.");
    }

    // check 2: subscription must not be free trial
    if ($subscription->isFreeTrial()) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason('free trial subscription can not be refunded');
    }

    // find subscription invoice
    /** @var Invoice|null $subscriptionInvoice */
    $subscriptionInvoice = $subscription->invoices()
      ->whereIn('type', [Invoice::TYPE_NEW_SUBSCRIPTION, Invoice::TYPE_RENEW_SUBSCRIPTION])   // subscription invoice
      ->where('period', $subscription->current_period) // current period
      ->whereIn('status', [
        Invoice::STATUS_COMPLETED,
        Invoice::STATUS_PROCESSING,
        Invoice::STATUS_PARTLY_REFUNDED,
        Invoice::STATUS_REFUND_FAILED,
        Invoice::STATUS_REFUNDING,
      ]) // confirmed
      ->whereNotIn('dispute_status', [
        Invoice::DISPUTE_STATUS_DISPUTING,
        Invoice::DISPUTE_STATUS_DISPUTED,
      ]) // not in dispute
      ->where('total_amount', '>', 0) // not free trial
      ->where('invoice_date', '>=', now()->subDays(14)) // within 14 days
      ->first();

    // update invoice and check again
    if ($subscriptionInvoice) {
      self::updateInvoicesFromDrOrder($subscriptionInvoice);
      if ($subscriptionInvoice->available_to_refund_amount < 0.01) {
        $subscriptionInvoice = null;
      }
    }

    // if not first period, the previous must be free trial
    if ($subscriptionInvoice) {
      if ($subscriptionInvoice->period == 2) {
        // the invoice must be the first paid subscripiton invoice
        if (
          $subscription->invoices()
          ->whereIn('type', [Invoice::TYPE_NEW_SUBSCRIPTION, Invoice::TYPE_RENEW_SUBSCRIPTION])
          ->where('period', $subscription->current_period - 1)
          ->where('total_amount', '>', 0)
          ->count() > 0
        ) {
          $subscriptionInvoice = null;
        }
      } else if ($subscriptionInvoice->period > 2) {
        // if period > 2, then no subscription invoice can be refunded
        $subscriptionInvoice = null;
      }
    }

    // there must be no refund history for the product
    if ($subscriptionInvoice) {
      if (
        $subscription->user->type !== User::TYPE_STAFF &&
        self::isSubscriptionRefundedBefore($subscription->user, $subscription->plan_info['product_name'])
      ) {
        $subscriptionInvoice = null;
      }
    }

    // if no refundable invoice found
    if (!$subscriptionInvoice) {
      return (new RefundableResult())
        ->setRefundable(false)
        ->setReason('no refundable invoice found');
    } else {
      return (new RefundableResult())
        ->setRefundable(true)
        ->appendInvoices($subscriptionInvoice);
    }
  }

  /**
   * @param Invoice|Invoice[] $invoices
   */
  static public function updateInvoicesFromDrOrder(Invoice|array $invoices): void
  {
    /**
     * @var DigitalRiverService $service
     */
    $service = app(DigitalRiverService::class);

    $invoices = is_array($invoices) ? $invoices : [$invoices];
    foreach ($invoices as $invoice) {
      $drOrder = $service->getOrder($invoice->getDrOrderId());
      $invoice->fillFromDrObject($drOrder)->save();
    }
  }
}
