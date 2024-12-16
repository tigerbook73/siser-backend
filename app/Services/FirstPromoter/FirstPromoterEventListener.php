<?php

namespace App\Services\FirstPromoter;

use App\Events\SubscriptionOrderEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class FirstPromoterEventListener implements ShouldQueue
{
  /**
   * Create the event listener.
   */
  public function __construct(public FirstPromoterService $service) {}

  /**
   * Handle the event.
   */
  public function handle(SubscriptionOrderEvent $event): void
  {
    switch ($event->type) {
      case SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED:
        if ($event->invoice->subtotal <= 0) {
          break;
        }

        $this->service->trackSale(
          $event->invoice->user_id,
          $event->invoice->id,
          $event->invoice->subtotal - $event->invoice->discount,
          $event->invoice->currency,
          config('affiliate.first_promoter.plan_mapping')[$event->invoice->plan_info['interval']] ?? null,
          $event->invoice->coupon_info['code'] ?? null,
        );
        break;

      case SubscriptionOrderEvent::TYPE_ORDER_REFUNDED:
        if ($event->invoice->subtotal <= 0) {
          break;
        }

        $amount = $event->refund ?
          $event->refund->amount * $event->invoice->subtotal / $event->invoice->total_amount :  // partial refund
          $event->invoice->subtotal; // full refund
        if ($amount <= 0) {
          break;
        }

        $this->service->trackRefund(
          $event->invoice->user_id,
          $event->invoice->id,
          $event->refund ? $event->refund->id : 'none', // none - force refund without refund object, e.g. chargeback
          $amount,
          $event->invoice->currency,
        );
        break;

      default:
        break;
    }
  }
}
