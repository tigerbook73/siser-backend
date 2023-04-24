<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\DrEvent;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\ApiException as DrApiException;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\ObjectSerializer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionManagerDR implements SubscriptionManager
{
  public $drService;
  public $eventHandlers = [];

  public function __construct()
  {
    $this->drService = new DigitalRiverService();

    $this->eventHandlers = [
      // order events
      'order.accepted'                => ['class' => DrOrder::class,        'handler' => 'onOrderAccepted'],
      'order.blocked'                 => ['class' => DrOrder::class,        'handler' => 'onOrderBlocked'],
      'order.cancelled'               => ['class' => DrOrder::class,        'handler' => 'onOrderCancelled'],
      'order.charge.failed'           => ['class' => DrOrder::class,        'handler' => 'onOrderChargeFailed'],
      'order.charge.capture.complete' => ['class' => DrCharge::class,       'handler' => 'onOrderChargeCaptureComplete'],
      'order.charge.capture.failed'   => ['class' => DrCharge::class,       'handler' => 'onOrderChargeCaptureFailed'],
      'order.complete'                => ['class' => DrOrder::class,        'handler' => 'onOrderComplete'],
      'order.invoice.created'         => ['class' => 'array',               'handler' => 'onOrderInvoiceCreated'],
      'order.chargeback'              => ['class' => DrOrder::class,        'handler' => 'onOrderChargeback'],

      // TODO: refund
      // 

      'subscription.extended'       => ['class' => 'array',                 'handler' => 'onSubscriptionExtended'],
      'subscription.failed'         => ['class' => DrSubscription::class,   'handler' => 'onSubscriptionFailed'],
      'subscription.payment_failed' => ['class' => 'array',                 'handler' => 'onSubscriptionPaymentFailed'],
      'subscription.reminder'       => ['class' => 'array',                 'handler' => 'onSubscriptionReminder'],
      // TODO: 
      // 'subscription.updated'              => ['class' => DrSubscription::class, 'handler' => 'doNothing'],
    ];
  }

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
    Log::info("Subscription: $subscription->id: $subscription->status: create subscription (init)");

    // create checkout
    try {
      $checkout = $this->drService->createCheckout($subscription);
    } catch (\Throwable $th) {
      $subscription->delete();
      Log::info("Subscription: $subscription->id: $subscription->status: delete subscription (init)");
      throw $th;
    }
    Log::info("Subscription: $subscription->id: $subscription->status: create dr-checkout");

    // update subscription
    $subscription->subtotal = $checkout->getSubtotal();
    $subscription->total_tax = $checkout->getTotalTax();
    $subscription->total_amount = $checkout->getTotalAmount();
    $subscription->dr = [
      'checkout_id' => $checkout->getId(),
      'checkout_payment_session_id' => $checkout->getPayment()->getSession()->getId(),
    ];
    $subscription->save();
    Log::info("Subscription: $subscription->id: $subscription->status: create subscription (with dr-checkout)");

    return $subscription;
  }

  public function deleteSubscription(Subscription $subscription): bool
  {
    if ($subscription->status != 'draft') {
      throw new Exception('Try to delete subscription not in draft status', 500);
    }

    try {
      if (isset($subscription->dr['checkout_id'])) {
        $this->drService->deleteCheckoutAsync($subscription->dr['checkout_id']);
        Log::info("Subscription: $subscription->id: $subscription->status: delete dr-checkout");
      }
      if (isset($subscription->dr_subscription_id)) {
        $this->drService->deleteSubscriptionAsync($subscription->dr_subscription_id);
        Log::info("Subscription: $subscription->id: $subscription->status: delete dr-subscription");
      }
    } catch (\Throwable $th) {
    }

    Log::info("Subscription: $subscription->id: $subscription->status: delete subscription");
    return $subscription->delete();
  }

  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string|null $terms): Subscription
  {
    try {
      // attach source_id to checkout
      $this->drService->attachCheckoutSource($subscription->dr['checkout_id'], $paymentMethod->dr['source_id']);
      Log::info("Subscription: $subscription->id: $subscription->status: attach dr-source to dr-checkout");

      // update checkout terms
      if ($terms) {
        $this->drService->updateCheckoutTerms($subscription->dr['checkout_id'], $terms);
        Log::info("Subscription: $subscription->id: $subscription->status: update dr-checkout terms");
      }

      // convert checkout to order
      $order = $this->drService->convertCheckoutToOrder($subscription->dr['checkout_id']);
      Log::info("Subscription: $subscription->id: $subscription->status: convert dr-checkout to dr-order");

      // update subscription
      $subscription->dr_subscription_id = $order->getItems()[0]->getSubscriptionInfo()->getSubscriptionId();
      $subscription->dr = [
        'checkout_id' => $subscription->dr['checkout_id'],
        'checkout_payment_session_id' => $subscription->dr['checkout_payment_session_id'],
        'order_id' => $order->getId(),
        'subscription_id' => $subscription->dr_subscription_id,
      ];

      $subscription->status = 'pending';
      $subscription->sub_status = 'normal';
      $subscription->save();
      Log::info("Subscription: $subscription->id: $subscription->status: pay subscription");

      return $subscription;
    } catch (DrApiException $th) {
      $body = $th->getResponseObject()->getErrors()[0];
      throw (new Exception("{$body->getCode()}: {$body->getMessage()}", $th->getCode()));
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function cancelSubscription(Subscription $subscription): Subscription
  {
    try {
      $this->drService->cancelSubscription($subscription->dr_subscription_id);
      Log::info("Subscription: $subscription->id: $subscription->status: cancel dr-subscription");

      $subscription->sub_status = 'cancelling';
      $subscription->next_invoice_date = null;
      $subscription->save();
      Log::info("Subscription: $subscription->id: $subscription->status: cancel subscription");

      // send notification
      $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED);

      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  /**
   * customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo)
  {
    $user = $billingInfo->user;
    if (empty($user->dr['customer_id'])) {
      $customer = $this->drService->createCustomer($billingInfo);
      $user->dr = ['customer_id' => $customer->getId()];
      $user->save();
      Log::info("Customer: $user->id: create dr-customer billing information");
    } else {
      $customer = $this->drService->updateCustomer($user->dr['customer_id'], $billingInfo);
      Log::info("Customer: $user->id: update dr-customer billing information");
    }

    return $customer;
  }

  /**
   * payment management
   */
  public function updatePaymentMethod(User $user, string $sourceId): PaymentMethod
  {
    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = $user->payment_method()->first();
    $previousSourceId = $paymentMethod ? $paymentMethod->dr['source_id'] : null;

    try {
      $source = $this->drService->getSource($sourceId);
    } catch (\Throwable $th) {
      throw new Exception('invalid source id', 400);
    }

    // attach source to customer
    $this->drService->attachCustomerSource($user->dr['customer_id'], $source->getId());
    Log::info("Customer: $user->id: attach dr-source to dr-customer");

    // attach source to active subscription

    /** @var Subscription|null $subscription */
    $subscription = $user->subscriptions()
      ->where('status', 'active')
      ->where('subscription_level', '>', 1)
      ->first();
    if ($subscription) {
      $this->drService->updateSubscriptionSource($subscription->dr_subscription_id, $source->getId());
      Log::info("Subscription: $subscription->id: $subscription->status: attach new dr-source to dr-subscription");
    }

    // deatch previous source
    if ($previousSourceId) {
      try {
        $this->drService->dettachCustomerSource($user->dr['customer_id'], $previousSourceId);
        Log::info("Subscription: $subscription->id: $subscription->status: detach old dr-source from dr-subscription");
      } catch (\Throwable $th) {
      }
    }

    // create / update payment method
    if (!$paymentMethod) {
      $paymentMethod = new PaymentMethod();
      $paymentMethod->id      = $user->id;
      $paymentMethod->user_id = $user->id;
    }
    $paymentMethod->type          = $source->getType();
    $paymentMethod->dr            = ['source_id' => $sourceId];
    $paymentMethod->display_data  = ($source->getType() == 'creditCard') ?  [
      'last_four_digits'  => $source->getCreditCard()->getLastFourDigits(),
      'brand'             => $source->getCreditCard()->getBrand(),
    ] : null;

    DB::transaction(function () use ($user, $paymentMethod, $source, $subscription,) {
      $paymentMethod->save();
      Log::info("Customer: $user->id: update payment-method");

      if ($subscription) {
        $subscriptionDr = $subscription->dr;
        $subscriptionDr['source_id'] = $source->getId();
        $subscription->dr = $subscriptionDr;
        $subscription->save();
        Log::info("Subscription: $subscription->id: $subscription->status: update subscription.dr.source_id");
      }
    });

    return $paymentMethod;
  }


  /**
   * webhook event
   */

  public function updateDefaultWebhook(bool $enable)
  {
    $this->drService->updateDefaultWebhook(array_keys($this->eventHandlers), $enable);
  }

  public function webhookHandler(array $event): bool
  {
    $eventInfo = ['id' => $event['id'], 'type' => $event['type']];
    if (DrEvent::exists($event['id'])) {
      Log::info('DR event duplicated:', $eventInfo);
      return true;
    }

    Log::info('DR event received:', $eventInfo);

    $eventHandler = $this->eventHandlers[$event['type']] ?? null;
    if ($eventHandler && method_exists($this, $eventHandler['handler'])) {
      try {
        $object = ObjectSerializer::deserialize($event['data']['object'], $eventHandler['class']);
        $handler = $eventHandler['handler'];
        $subscription = $this->$handler($object);
        $eventInfo['subscription_id'] = $subscription?->id;
      } catch (\Throwable $th) {
        Log::info($th);
        return false;
      }
    } else {
      Log::info('DR event has not handler', $eventInfo);
    }

    Log::info("DR event processed: ", $eventInfo);
    DrEvent::log($eventInfo);
    return true;
  }

  /**
   * order event handlers
   */
  protected function validateOrder(DrOrder $order, array $options = ['be_first' => true]): Subscription|null
  {
    // must be a subscription order
    $drSubscriptionId = $order->getItems()[0]->getSubscriptionInfo()?->getSubscriptionId();
    if (!$drSubscriptionId) {
      Log::warning(__FUNCTION__ . ': skip order that does not contains an subscription id');
      return null;
    }

    // validate the subscription
    $subscription = Subscription::where('dr_subscription_id', $drSubscriptionId)->first();
    if (!$subscription) {
      Log::warning(__FUNCTION__ . ': skip invalid subscription', ['object' => $order]);
      return null;
    }

    // only process the first order
    if ($options['be_first'] ?? false) {
      if (!isset($subscription->dr['order_id']) || $subscription->dr['order_id'] != $order->getId()) {
        Log::warning(__FUNCTION__ . ': skip non-first subscription', ['object' => $order]);
        return null;
      }
    }

    return $subscription;
  }

  public function onOrderAccepted(DrOrder $order): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // must be in pending status
    if ($subscription->status != 'pending') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending');
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: order.accepted");

    // fulfill order
    try {
      $this->drService->fulfillOrder($order->getId());
      Log::info("Subscription: $subscription->id: $subscription->status: fulfill dr-order");
    } catch (\Throwable $th) {
      $subscription->stop('failed', 'fulfill first order failed');
      Log::info("Subscription: $subscription->id: $subscription->status: fulfill dr-order failed");

      $subscription->sendNotification(SubscriptionNotification::NOTIF_ABORTED);
      return null;
    }

    // update subscription status
    $subscription->status = 'processing';
    $subscription->sub_status = 'normal';
    $subscription->save();
    Log::info("Subscription: $subscription->id: $subscription->status: fulfill subscription");

    return $subscription;
  }

  public function onOrderBlocked(DrOrder $order): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // must be in pending status
    if ($subscription->status != 'pending' && $subscription->status != 'processing') {
      Log::info(__FUNCTION__ . ': skip subscription not in pending or processing');
      return null;
    }

    // update subscription status
    $subscription->stop('failed', 'first order being blocked');
    Log::info("Subscription: $subscription->id: $subscription->status: order.blocked");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ABORTED);

    return $subscription;
  }

  public function onOrderCancelled(DrOrder $order): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == 'failed') {
      Log::info(__FUNCTION__ . ': skip subscription already in failed');
      return null;
    }

    $subscription->stop('failed', 'first order being cancelled');
    Log::info("Subscription: $subscription->id: $subscription->status: order.cancelled");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ABORTED);

    return $subscription;
  }

  public function onOrderChargeFailed(DrOrder $order): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == 'failed') {
      Log::info(__FUNCTION__ . ': skip subscription already in failed');
      return null;
    }

    $subscription->stop('failed', 'first order failed');
    Log::info("Subscription: $subscription->id: $subscription->status: order.charge.failed");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ABORTED);

    return $subscription;
  }

  public function onOrderChargeCaptureComplete(DrCharge $charge): Subscription|null
  {
    // validate the order
    $order = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: order.charge.capture.complete");

    // do nothing, just wait for order.complete event

    return $subscription;
  }

  public function onOrderChargeCaptureFailed(DrCharge $charge): Subscription|null
  {
    // validate the order
    $order = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == 'failed') {
      Log::info(__FUNCTION__ . ': skip subscription already in failed');
      return null;
    }

    $subscription->stop('failed', 'first order charge capture failed');
    Log::info("Subscription: $subscription->id: $subscription->status: order.charge.capture.failed");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ABORTED);

    return $subscription;
  }

  public function onOrderComplete(DrOrder $order): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($order);
    if (!$subscription) {
      return null;
    }

    // TODO: for non first order, update subscription's total_tax/total_amount

    // must be in processing status
    if ($subscription->status != 'processing') {
      Log::info(__FUNCTION__ . ': skip subscription not in processing');
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: order.complete");

    // activate dr subscription
    $drSubscription = $this->drService->activateSubscription($subscription->dr_subscription_id);
    Log::info("Subscription: $subscription->id: $subscription->status: activate dr-subscription");

    // stop previous subscription and start new subscription
    DB::transaction(function () use ($subscription, $drSubscription) {
      $user = $subscription->user;

      // stop previous subscription
      $previousSubscription = $user->getActiveSubscription();
      if ($previousSubscription) {
        $previousSubscription->stop('inactive', 'new subscrption activated');
      }

      // active current subscription
      $subscription->start_date = now();
      $subscription->current_period = 1;
      $subscription->current_period_start_date = now();
      $subscription->current_period_end_date =
        $drSubscription->getCurrentPeriodEndDate() ? Carbon::parse($drSubscription->getCurrentPeriodEndDate()) : null;
      $subscription->next_invoice_date =
        $drSubscription->getNextInvoiceDate() ? Carbon::parse($drSubscription->getNextInvoiceDate()) : null;
      $subscription->status = 'active';
      $subscription->sub_status = 'normal';
      $subscription->save();

      // update user subscription level and license_count
      $user->subscription_level = $subscription->subscription_level;
      $user->save();

      Log::info("Subscription: $subscription->id: $subscription->status: activate subscription");
    });

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_CONFIRMED);

    return $subscription;
  }

  public function onOrderInvoiceCreated(array $orderInvoice): Subscription|null
  {
    // validate order
    $order = $this->drService->getOrder($orderInvoice['orderId']);
    $subscription = $this->validateOrder($order, []);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != 'active') {
      Log::warning("Subscription: $subscription->id: $subscription->status: skip order.invoice.created");
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: order.invoice.created");

    // create invoice pdf download link TODO: avoid duplicated invoice
    $fileLink = $this->drService->createFileLink($orderInvoice['fileId'], now()->addYear());
    Log::info("Subscription: $subscription->id: $subscription->status: create dr-invoice-file-link");

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
    $invoice->pdf_file = $fileLink->getUrl();
    $invoice->dr = [
      'order_id'  => $orderInvoice['orderId'],
      'file_id'   => $orderInvoice['fileId'],
    ];
    $invoice->status = 'completed';
    $invoice->save();
    Log::info("Subscription: $subscription->id: $subscription->status: create period invoice"); // TODO: period

    // sent notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_INVOICE_PDF, $invoice);

    return $subscription;
  }

  public function onOrderChargeback(DrOrder $order): Subscription|null
  {
    $subscription = $this->validateOrder($order, []);

    // must be in active status
    if ($subscription->status != 'active') {
      Log::info(__FUNCTION__ . ': skip subscription not in active status');
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: order.chargeback");

    DB::transaction(function () use ($subscription) {

      $subscription->stop('failed', 'charge back');
      Log::info("Subscription: $subscription->id: $subscription->status: charge back:");

      $user = $subscription->user;

      // TODO: refactor
      if ($user->machines()->count() > 0) {
        $basicSubscription = Subscription::createBasicMachineSubscription($user);
        $user->subscription_level = $basicSubscription->subscription_level;
      } else {
        $user->subscription_level = 0;
      }
      $user->save();
    });

    return $subscription;
  }

  /**
   * subscription event handlers
   */

  protected function validateSubscription(DrSubscription $drSubscription): Subscription|null
  {
    // validate the subscription
    $subscription = Subscription::where('dr_subscription_id', $drSubscription->getId())->first();
    if (!$subscription) {
      Log::warning(__FUNCTION__ . ': skip invalid subscription', ['object' => $drSubscription]);
      return null;
    }

    return $subscription;
  }

  public function onSubscriptionExtended(array $event): Subscription|null
  {
    /** @var DrSubscription $drSubscription */
    $drSubscription = ObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    // $drInvoice = ObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $drSubscription]);
      return null;
    }

    // update subscription data
    $subscription->current_period = $subscription->current_period + 1; // TODO: more to check
    $subscription->current_period_start_date = $subscription->current_period_end_date;
    $subscription->current_period_end_date = Carbon::parse($drSubscription->getCurrentPeriodEndDate());
    $subscription->next_invoice_date = Carbon::parse($drSubscription->getNextInvoiceDate());
    $subscription->save();
    Log::info("Subscription: $subscription->id: $subscription->status: subscription extended");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_EXTENDED);

    return $subscription;
  }

  public function onSubscriptionFailed(DrSubscription $drSubscription): Subscription|null
  {
    $subscription = $this->validateSubscription($drSubscription);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $drSubscription]);
      return null;
    }

    // stop subscription data
    $subscription->stop('failed', ' renew charge failed');
    Log::info("Subscription: $subscription->id: $subscription->status: subscription stoped");

    // activate default subscription
    $user = $subscription->user;
    if ($user->machines()->count() > 0) {
      $basicSubscription = Subscription::createBasicMachineSubscription($user);
      $user->subscription_level = $basicSubscription->subscription_level;
    } else {
      $user->subscription_level = 0;
    }
    $user->save();
    Log::info("User: $user->id: update user subscription_level to $user->subscription_level");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_FAILED);

    return $subscription;
  }

  public function onSubscriptionPaymentFailed(array $event): Subscription|null
  {
    $drSubscription = ObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    // $drInvoice = ObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $drSubscription]);
      return null;
    }

    $subscription->sub_status = 'overdue';
    $subscription->save();
    Log::info("Subscription: $subscription->id: $subscription->status: subscription.payment.failed");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_OVERDUE);

    return $subscription;
  }

  public function onSubscriptionReminder(array $event): Subscription|null
  {
    $drSubscription = ObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    // $drInvoice = ObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != 'active') {
      Log::warning(__FUNCTION__ . ': skip inactive subscription', ['object' => $drSubscription]);
      return null;
    }

    Log::info("Subscription: $subscription->id: $subscription->status: subscription.reminer");

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);

    return $subscription;
  }

  /**
   * helper function
   */

  public function activateDefaultSubscription(User $user)
  {
    if ($user->machines()->count() > 0) {
      $basicSubscription = Subscription::createBasicMachineSubscription($user);
      $user->subscription_level = $basicSubscription->subscription_level;
    } else {
      $user->subscription_level = 0;
    }
    $user->save();
  }

  // TODO: stop subscription when end-date comes.
}
