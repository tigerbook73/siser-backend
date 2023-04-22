<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\GeneralConfiguration;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use Illuminate\Support\Facades\Log;

class SubscriptionManagerMockup implements SubscriptionManager
{
  public function createSubscription(User $user, Plan $plan, Coupon|null $coupon): Subscription
  {
    $billingInfo = $user->billing_info;
    $country = Country::findByCode($billingInfo->address['country']);
    $publicPlan = $plan->toPublicPlan($billingInfo->address['country']);
    $couponDiscount = $coupon->percentage_off ?? 0;

    // create subscription
    $subscription = new Subscription();
    $subscription->user_id                    = $user->id;
    $subscription->plan_id                    = $plan->id;
    $subscription->coupon_id                  = $coupon->id ?? null;
    $subscription->billing_info               = $billingInfo->toResource('customer');
    $subscription->plan_info                  = $publicPlan;
    $subscription->coupon_info                = $coupon ? $coupon->toResource('customer') : null;
    $subscription->processing_fee_info        = [
      'processing_fee_rate'     => $country->processing_fee_rate,
      'explicit_processing_fee' => $country->explicit_processing_fee,
    ];
    $subscription->currency                   = $country->currency;
    if ($country->explicit_processing_fee) {
      $subscription->price                    = round($publicPlan['price']['price'] * (1 -  $couponDiscount / 100), 2);
      $subscription->processing_fee           = round($subscription->price * $country->processing_fee_rate / 100, 2);
    } else {
      $subscription->price                    = round($publicPlan['price']['price'] * (1 -  $couponDiscount / 100) * (1 + $country->processing_fee_rate / 100), 2);
      $subscription->processing_fee           = 0;
    }
    $subscription->start_date                 = null;
    $subscription->end_date                   = null;
    $subscription->subscription_level         = $publicPlan['subscription_level'];
    $subscription->current_period             = 0;
    $subscription->current_period_start_date  = null;
    $subscription->current_period_end_date    = null;
    $subscription->next_invoice_date          = null;
    $subscription->stop_reason                = null;
    $subscription->status                     = 'draft';
    $subscription->sub_status                 = 'normal';

    $subscription->save();

    // create checkout
    // skip
    // update subscription
    $subscription->subtotal = round($subscription->price + $subscription->processing_fee, 2);
    $subscription->total_tax = round($subscription->subtotal * 0.1, 2);
    $subscription->total_amount = round($subscription->subtotal + $subscription->total_tax, 2);
    $subscription->dr = [
      'checkout_id' => 'checkout_id_' . uuid_create(),
      'checkout_payment_session_id' => 'session_id_' . uuid_create(),
    ];
    $subscription->save();

    return $subscription;
  }

  public function deleteSubscription(Subscription $subscription): bool
  {
    return $subscription->delete();
  }

  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string|null $terms): Subscription
  {
    // update subscription
    $subscription->dr_subscription_id = 'subscription_id_' . uuid_create();
    $subscription->dr = [
      'checkout_id'                 => $subscription->dr['checkout_id'],
      'checkout_payment_session_id' => $subscription->dr['checkout_payment_session_id'],
      'order_id'                    => 'order_id_' . uuid_create(),
      'subscription_id'             => $subscription->dr_subscription_id,
    ];
    $subscription->status = 'pending';
    $subscription->sub_status = 'normal';
    $subscription->save();

    // TEST: on success
    dispatch(fn () => $this->onOrderAccepted($subscription))->afterResponse();
    dispatch(fn () => $this->onOrderComplete($subscription))->afterResponse();
    dispatch(fn () => $this->onOrderInvoiceCreated($subscription))->afterResponse();

    return $subscription;
  }

  public function cancelSubscription(Subscription $subscription): Subscription
  {
    $subscription->sub_status = 'cancelling';
    $subscription->next_invoice_date = null;
    $subscription->save();

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED);

    return $subscription;
  }

  /**
   * customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo)
  {
    $user = $billingInfo->user;
    if (empty($user->dr['customer_id'])) {
      $user->dr = ['customer_id' => 'customer_id_' . uuid_create()];
      $user->save();
    }
  }

  /**
   * payment management
   */
  public function updatePaymentMethod(User $user, string $sourceId): PaymentMethod
  {
    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = $user->payment_method()->first();

    // attach source to customer
    // skip

    // attach source to active subscription

    /** @var Subscription|null $subscription */
    $subscription = $user->subscriptions()
      ->where('status', 'active')
      ->where('subscription_level', '>', 1)
      ->first();

    // deatch previous source

    // create / update payment method
    if (!$paymentMethod) {
      $paymentMethod = new PaymentMethod();
      $paymentMethod->id      = $user->id;
      $paymentMethod->user_id = $user->id;
    }
    $paymentMethod->type          = 'creditCard';
    $paymentMethod->dr            = ['source_id' => $sourceId];
    $paymentMethod->display_data  = [
      'last_four_digits'  => 'TEST',
      'brand'             => 'Master',
    ];

    $paymentMethod->save();

    if ($subscription) {
      $subscriptionDr = $subscription->dr;
      $subscriptionDr['source_id'] = $sourceId;
      $subscription->dr = $subscriptionDr;
      $subscription->save();
    }

    return $paymentMethod;
  }


  /**
   * webhook event handler
   */
  public function webhookHandler(array $event): bool
  {
    return true;
  }


  /**
   * order event handlers
   */
  public function onOrderAccepted(Subscription $subscription): Subscription|null
  {
    // must be in pending status
    if ($subscription->status != 'pending') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending');
      return null;
    }

    // update subscription status
    $subscription->status = 'processing';
    $subscription->sub_status = 'normal';
    $subscription->save();

    return $subscription;
  }

  public function onOrderBlocked(Subscription $subscription): Subscription|null
  {
    // must be in pending status
    if ($subscription->status != 'pending') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending');
      return null;
    }

    // update subscription status
    $subscription->status = 'failed';
    $subscription->sub_status = 'normal';
    $subscription->stop_reason = "first order being blocked";
    $subscription->save();

    // no notification required
    return $subscription;
  }

  public function onOrderCancelled(Subscription $subscription): Subscription|null
  {
    // must be in pending status
    if ($subscription->status != 'pending') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending');
      return null;
    }

    $subscription->status = 'failed';
    $subscription->sub_status = 'normal';
    $subscription->stop_reason = "first order being cancelled";
    $subscription->save();

    return $subscription;
  }

  public function onOrderChargeFailed(Subscription $subscription): Subscription|null
  {
    // must be in pending status
    if ($subscription->status != 'pending') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending');
      return null;
    }

    $subscription->status = 'cancelled';
    $subscription->sub_status = 'normal';
    $subscription->stop_reason = "first order being cancelled";
    $subscription->save();

    return $subscription;
  }

  public function onOrderComplete(Subscription $subscription): Subscription|null
  {
    // TODO: for non first order, update subscription's total_tax/total_amount

    // must be in processing status
    if ($subscription->status != 'processing') {
      Log::info(__FUNCTION__ . ': skip subscription not in processing');
      return null;
    }

    // activate dr subscription
    // skip

    // stop previous subscription and start new subscription
    $user = $subscription->user;

    // stop previous subscription
    $previousSubscription = $user->getActiveSubscription();
    if ($previousSubscription) {
      $previousSubscription->stop('stopped', 'new subscrption activated');
    }

    // active current subscription
    $subscription->start_date = now();
    $subscription->current_period = 1;
    $subscription->current_period_start_date = now();
    $subscription->current_period_end_date = now()->addDays(2);
    $subscription->next_invoice_date = $subscription->current_period_end_date->subDays(
      GeneralConfiguration::getConfiguration()->plan_billing_offset_days
    );
    $subscription->status = 'active';
    $subscription->sub_status = 'normal';
    $subscription->save();

    // update user subscription level and license_count
    $user->subscription_level = $subscription->subscription_level;
    $user->save();

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_CONFIRMED);

    return $subscription;
  }

  public function onOrderInvoiceCreated(Subscription $subscription): Subscription|null
  {
    $invoice = new Invoice();
    $invoice->user_id = $subscription->user_id;
    $invoice->subscription_id = $subscription->id;
    $invoice->period = $subscription->current_period;
    $invoice->currency = $subscription->currency;
    $invoice->plan = [
      "name" => $subscription->plan_info['name'],
      "price" => $subscription->plan_info['price']['price'],
    ];
    $invoice->coupon = $subscription->coupon_info ? [
      "code" => $subscription->coupon_info['code'],
      "percentage_off" => $subscription->coupon_info['percentage_off'],
    ] : null;
    $invoice->processing_fee = $subscription->processing_fee_info;
    $invoice->subtotal = $subscription->subtotal;
    $invoice->total_tax = $subscription->total_tax;
    $invoice->total_amount = $subscription->total_amount;
    $invoice->invoice_date = now();
    $invoice->pdf_file = '/robots.txt';
    $invoice->dr = ['file_id' => 'file_id_' . uuid_create()];
    $invoice->dr = [
      'order_id'  => 'order_id_' . uuid_create(),
      'file_id'   => 'file_id_' . uuid_create(),
    ];
    $invoice->status = 'completed';
    $invoice->save();

    // sent notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_INVOICE_PDF, $invoice);

    return $subscription;
  }

  public function onOrderChargeback(Subscription $subscription): Subscription|null
  {
    // must be in active status
    if ($subscription->status != 'active') {
      Log::info(__FUNCTION__ . ': skip subscription not in active status');
      return null;
    }

    $subscription->stop('failed', 'charge back');
    $user = $subscription->user;

    // TODO: refactor
    if ($user->machines()->count() > 0) {
      $basicSubscription = Subscription::createBasicMachineSubscription($user);
      $user->subscription_level = $basicSubscription->subscription_level;
    } else {
      $user->subscription_level = 0;
    }
    $user->save();

    return $subscription;
  }

  /**
   * subscription event handlers
   */

  public function onSubscriptionExtended(Subscription $subscription): Subscription|null
  {
    // update subscription data
    $subscription->current_period = $subscription->current_period + 1; // TODO: more to check
    $subscription->current_period_start_date = $subscription->current_period_end_date;
    $subscription->current_period_end_date = $subscription->current_period_start_date->addDays(2);
    $subscription->next_invoice_date = $subscription->current_period_end_date->subDays(
      GeneralConfiguration::getConfiguration()->plan_billing_offset_days
    );
    $subscription->save();

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_EXTENDED);

    return $subscription;
  }

  public function onSubscriptionFailed(Subscription $subscription): Subscription|null
  {
    if (!$subscription->status != 'active') {
      return null;
    }

    // stop subscription data
    $subscription->stop('failed', 'charge failed');

    // activate default subscription
    $user = $subscription->user;
    if ($user->machines()->count() > 0) {
      $basicSubscription = Subscription::createBasicMachineSubscription($user);
      $user->subscription_level = $basicSubscription->subscription_level;
    } else {
      $user->subscription_level = 0;
    }
    $user->save();

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_FAILED);

    return $subscription;
  }

  public function onSubscriptionPaymentFailed(Subscription $subscription): Subscription|null
  {
    if (!$subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $subscription->id]);
      return null;
    }

    $subscription->sub_status = 'overdue';
    $subscription->save();

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_OVERDUE);

    return $subscription;
  }

  public function onSubscriptionReminder(Subscription $subscription): Subscription|null
  {
    if (!$subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $subscription->id]);
      return null;
    }
    // send reminder to customer
    // notifyu customer if credit card to be expired

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);

    return $subscription;
  }
}
