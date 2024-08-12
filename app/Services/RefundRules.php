<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RefundRules
{
  static public function invoiceRefundable(Invoice $invoice): array
  {
    if (
      $invoice->dispute_status == Invoice::DISPUTE_STATUS_DISPUTING ||
      $invoice->dispute_status == Invoice::DISPUTE_STATUS_DISPUTED
    ) {
      return ['refundable' => false, 'reason' => 'invoice is in disputing or disputed'];
    }

    if ($invoice->status == Invoice::STATUS_REFUNDED) {
      return ['refundable' => false, 'reason' => 'invoice is already refunded'];
    }

    if (
      $invoice->status != Invoice::STATUS_COMPLETED &&
      $invoice->status != Invoice::STATUS_PROCESSING &&
      $invoice->status != Invoice::STATUS_PARTLY_REFUNDED
    ) {
      return ['refundable' => false, 'reason' => "invoice in {$invoice->status} can not be refunded."];
    }

    if ($invoice->total_amount == 0) {
      return ['refundable' => false, 'reason' => 'invoice with total_amount = 0 can not be refunded'];
    }

    return ['refundable' => true, 'reason' => ''];
  }

  static public function customerRefundable(Subscription $subscription): array
  {
    // check 1: subscription must be active
    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      return ['refundable' => false, 'reason' => "subscription in {$subscription->status} can not be refunded."];
    }

    // check 2: there is an invoice for current period
    $invoice = $subscription->getCurrentPeriodInvoice();
    if (!$invoice) {
      return ['refundable' => false, 'reason' => 'invoice not found'];
    }

    // check 3: the invoice is refundable
    $result = self::invoiceRefundable($invoice);
    if (!$result['refundable']) {
      return $result;
    }

    // check 4: the invoice is the first paid invoice of the subscription and within 14 days
    if (
      $invoice->period > 1 &&
      $subscription->invoices()
      ->where('period', $invoice->period - 1)
      ->where('total_amount', '>', 0)
      ->count() > 0
    ) {
      return ['refundable' => false, 'reason' => 'the invoice is not the first paid invoice of the subscription'];
    }

    // check 5: 14 days passed
    if ($invoice->getStatusTimestamp(Invoice::STATUS_COMPLETED)?->diffInDays(now()) > 14) {
      return ['refundable' => false, 'reason' => 'invoices can only be refunded within 14 days after payment'];
    }

    // check 6: staff user can refund multiple times
    if (config('siser.staff_refund') && $invoice->user->type == User::TYPE_STAFF) {
      return ['refundable' => true, 'reason' => 'staff customer can refund mutiple times', 'invoice' => $invoice];
    }

    // check 7: customer has not refunded the same product before
    $refundCount = Refund::where('user_id', $invoice->user_id)  // @phpstan-ignore-line
      ->whereIn('status', [Refund::STATUS_COMPLETED, Refund::STATUS_PENDING])
      ->whereHas('invoice', fn(Builder $query) => $query->where('plan_info->id', $invoice->plan_info['id']))
      ->count();
    if ($refundCount > 0) {
      return ['refundable' => false, 'reason' => 'customer has already refunded the same product before'];
    }

    return ['refundable' => true, 'reason' => '', 'invoice' => $invoice];
  }
}
