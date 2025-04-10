<?php

namespace App\Models;

use App\Models\Base\SubscriptionLog as BaseSubscriptionLog;
use Carbon\Carbon;

class SubscriptionLog extends BaseSubscriptionLog
{
  public const SUBSCRIPTION_ACTIVATED     = 'subscription.activated';
  public const SUBSCRIPTION_CANCELLED     = 'subscription.cancelled';
  public const SUBSCRIPTION_CONVERTED     = 'subscription.converted';  // converted from free trial to paid
  public const SUBSCRIPTION_EXTENDED      = 'subscription.extended';
  public const SUBSCRIPTION_FAILED        = 'subscription.failed';
  public const SUBSCRIPTION_STOPPED       = 'subscription.stopped';

  /**
   * log subscription event functions
   */

  static public function logEvent(string $event, Subscription $subscription, mixed $date = null)
  {
    $date = $date ? Carbon::parse($date) : now();
    $log = new SubscriptionLog();
    $log->user_id = $subscription->user_id;
    $log->event = $event;
    $log->date = $date;
    $log->date_time = $date;
    $log->data = [
      'subscription' => $subscription->toResource('admin'),
      'user' => [
        'id'                    => $subscription->user_id,
        'name'                  => $subscription->user->name,
        'subscription_level'    => $subscription->user->subscription_level,
        'machine_count'         => $subscription->user->machine_count,
        'seat_count'            => $subscription->user->seat_count,
        'type'                  => $subscription->user->type,
        'billing_email'         => $subscription->getBillingInfo()->email,
        'billing_country'       => $subscription->getBillingInfo()->address->country,
      ]
    ];
    $log->save();
  }
}
