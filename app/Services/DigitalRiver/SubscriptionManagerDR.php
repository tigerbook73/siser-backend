<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\DrEvent;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\RefundRules;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CustomerTaxIdentifier as DrCustomerTaxIdentifier;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\TaxIdentifier as DrTaxId;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * DrLog class
 */
class DrLog
{
  static public function log(string $level, string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    if ($context instanceof Subscription) {
      $context = ['subscription_id' => $context->id, 'subscription_status' => $context->status];
    } else if ($context instanceof Invoice) {
      $context = ['subscription_id' => $context->subscription_id, 'invoice_id' => $context->id, 'invoice_status' => $context->status];
    } else if ($context instanceof User) {
      $context = ['user_id' => $context->id, 'subscription_level' => $context->subscription_level];
    }
    Log::log($level, 'DR_LOG: ' . $location . ': ' . $action . ($context ? ':' : ''), $context);
  }

  static public function info(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    self::log(__FUNCTION__, $location, $action, $context);
  }

  static public function warning(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    self::log(__FUNCTION__, $location, $action, $context);
  }

  static public function error(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    self::log(__FUNCTION__, $location, $action, $context);
  }
}

class SubscriptionManagerDR implements SubscriptionManager
{
  public $eventHandlers = [];

  public function __construct(public DigitalRiverService $drService)
  {
    $this->eventHandlers = [
      // order events
      'order.accepted'                => ['class' => DrOrder::class,        'handler' => 'onOrderAccepted'],
      'order.blocked'                 => ['class' => DrOrder::class,        'handler' => 'onOrderBlocked'],
      'order.cancelled'               => ['class' => DrOrder::class,        'handler' => 'onOrderCancelled'],
      'order.charge.failed'           => ['class' => DrOrder::class,        'handler' => 'onOrderChargeFailed'],
      'order.charge.capture.complete' => ['class' => DrCharge::class,       'handler' => 'onOrderChargeCaptureComplete'],
      'order.charge.capture.failed'   => ['class' => DrCharge::class,       'handler' => 'onOrderChargeCaptureFailed'],
      'order.complete'                => ['class' => DrOrder::class,        'handler' => 'onOrderComplete'],
      'order.chargeback'              => ['class' => DrOrder::class,        'handler' => 'onOrderChargeback'],
      'order.refunded'                => ['class' => DrOrder::class,        'handler' => 'onOrderRefunded'],

      // subscription events
      'subscription.extended'         => ['class' => 'array',               'handler' => 'onSubscriptionExtended'],
      'subscription.failed'           => ['class' => DrSubscription::class, 'handler' => 'onSubscriptionFailed'],
      'subscription.payment_failed'   => ['class' => 'array',               'handler' => 'onSubscriptionPaymentFailed'],
      'subscription.reminder'         => ['class' => 'array',               'handler' => 'onSubscriptionReminder'],

      // invoice events: see Invoice.md for state machine
      'order.invoice.created'         => ['class' => 'array',               'handler' => 'onOrderInvoiceCreated'],
      'order.credit_memo.created'     => ['class' => 'array',               'handler' => 'onOrderCreditMemoCreated'],

      // refund events
      'refund.pending'                => ['class' => DrOrderRefund::class,  'handler' => 'onRefundPending'],
      'refund.failed'                 => ['class' => DrOrderRefund::class,  'handler' => 'onRefundFailed'],
      'refund.complete'               => ['class' => DrOrderRefund::class,  'handler' => 'onRefundComplete'],

      // tax id
      'tax_identifier.verified'       => ['class' => DrTaxId::class,        'handler' => 'onTaxIdStateChange'],
      'tax_identifier.not_valid'      => ['class' => DrTaxId::class,        'handler' => 'onTaxIdStateChange'],
    ];
  }

  protected function fillSubscriptionAmount(Subscription $subscription, DrCheckout|DrOrder|DrInvoice $drOrder)
  {
    $subscription->subtotal = $drOrder->getSubtotal();
    $subscription->tax_rate = $drOrder->getItems()[0]->getTax()->getRate();
    $subscription->total_tax = $drOrder->getTotalTax();
    $subscription->total_amount = $drOrder->getTotalAmount();
  }

  protected function fillSubscriptionNextAmount(Subscription $subscription, DrInvoice $drInvoice)
  {
    $next_invoice = $subscription->next_invoice;
    $next_invoice['subtotal'] = $drInvoice->getSubtotal();
    $next_invoice['tax_rate'] = $drInvoice->getItems()[0]->getTax()->getRate();
    $next_invoice['total_tax'] = $drInvoice->getTotalTax();
    $next_invoice['total_amount'] = $drInvoice->getTotalAmount();

    $subscription->next_invoice = $next_invoice;
  }

  public function createSubscription(User $user, Plan $plan, Coupon|null $coupon = null, TaxId|null $taxId = null): Subscription
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
    $subscription->billing_info               = $billingInfo->info();
    $subscription->tax_id_info                = $taxId ? $taxId->info() : null;
    $subscription->plan_info                  = $publicPlan;
    $subscription->coupon_info                = $coupon ? $coupon->toResource('customer') : null;
    $subscription->currency                   = $country->currency;
    $subscription->price                      = round($publicPlan['price']['price'] * (1 -  $couponDiscount / 100), 2);
    $subscription->start_date                 = null;
    $subscription->end_date                   = null;
    $subscription->subscription_level         = $publicPlan['subscription_level'];
    $subscription->current_period             = 0;
    $subscription->current_period_start_date  = null;
    $subscription->current_period_end_date    = null;
    $subscription->next_invoice_date          = null;
    $subscription->stop_reason                = null;
    $subscription->sub_status                 = Subscription::SUB_STATUS_NORMAL;
    $subscription->setStatus(Subscription::STATUS_DRAFT);

    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription (init) created => draft', $subscription);

    $invoice = $this->createFirstInvoice($subscription);
    DrLog::info(__FUNCTION__, 'create first invoice', $invoice);

    // create checkout
    try {
      $checkout = $this->drService->createCheckout($subscription);
      DrLog::info(__FUNCTION__, 'dr-checkout created', $subscription);
    } catch (\Throwable $th) {
      $invoice->delete();
      $subscription->delete();
      DrLog::info(__FUNCTION__, 'subscription/invoice deleted when creating dr-checkout fails', $subscription);

      throw $th;
    }

    // update subscription
    $this->fillSubscriptionAmount($subscription, $checkout);
    $subscription
      ->setDrCheckoutId($checkout->getId())
      ->setDrSubscriptionId($checkout->getItems()[0]->getSubscriptionInfo()->getSubscriptionId())
      ->setDrSessionId($checkout->getPayment()->getSession()->getId());
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription updated: amounts & dr', $subscription);

    return $subscription;
  }

  /** TODO: update: price/coupon/tax/items */
  public function updateSubscription(Subscription $subscription): Subscription
  {
    /*
    NOTE: subscrption can not be updated between remindered and extended
    */

    /*
    1. update $subscription.next_invoice (include items)
    2. update drSubscription
    */
    return $subscription;
  }

  public function deleteSubscription(Subscription $subscription): bool
  {
    if ($subscription->status != Subscription::STATUS_DRAFT) {
      throw new Exception('Try to delete subscription not in draft status', 500);
    }

    try {
      if (isset($subscription->dr['checkout_id'])) {

        $this->drService->deleteCheckout($subscription->dr['checkout_id']);
        DrLog::info(__FUNCTION__, 'dr-checkout deleted', $subscription);
      }
      if (isset($subscription->dr_subscription_id)) {
        $this->drService->deleteSubscription($subscription->dr_subscription_id);
        DrLog::info(__FUNCTION__, 'dr-subscription deleted', $subscription);
      }
    } catch (\Throwable $th) {
    }

    $invoice = $subscription->getActiveInvoice();
    $invoice->delete();
    $subscription->delete();
    DrLog::info(__FUNCTION__, 'subscription and invoice deleted', $subscription);

    return true;
  }

  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string $terms = null): Subscription
  {
    try {
      // attach source_id to checkout
      $this->drService->attachCheckoutSource($subscription->dr['checkout_id'], $paymentMethod->dr['source_id']);
      DrLog::info(__FUNCTION__, 'dr-source attached to dr-checkout', $subscription);

      // update checkout terms
      if ($terms) {
        $this->drService->updateCheckoutTerms($subscription->dr['checkout_id'], $terms);
        DrLog::info(__FUNCTION__, 'dr-checkout terms update', $subscription);
      }

      // convert checkout to order
      $order = $this->drService->convertCheckoutToOrder($subscription->dr['checkout_id']);
      DrLog::info(__FUNCTION__, 'dr-checkout converted to dr-order', $subscription);

      // update subscription
      $subscription->setDrOrderId($order->getId());
      $subscription->setStatus(Subscription::STATUS_PENDING);
      $subscription->sub_status = ($order->getState() == 'accepted') ? Subscription::SUB_STATUS_NORMAL : Subscription::SUB_STATUS_ORDER_PENDING;
      $subscription->save();
      DrLog::info(__FUNCTION__, 'subscription updated => pending', $subscription);

      // update invoice
      $invoice = $subscription->getActiveInvoice();
      $invoice->payment_method_info = $subscription->user->payment_method->info();
      $invoice->subtotal            = $subscription->subtotal;
      $invoice->total_tax           = $subscription->total_tax;
      $invoice->total_amount        = $subscription->total_amount;
      $invoice->invoice_date        = now();
      $invoice->setDrOrderId($order->getId());
      $invoice->setStatus(Invoice::STATUS_PENDING);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice init => pending', $invoice);

      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function cancelSubscription(Subscription $subscription, bool $needRefund = false, bool $immediate = false): Subscription
  {
    try {
      $refund = null;
      if ($needRefund) {
        $result = RefundRules::customerRefundable($subscription);
        if (!$result['refundable']) {
          throw new Exception('try to refund non-refundable invoice', 500);
        }
        $refund = $this->createRefund($result['invoice'], reason: 'cancel subscription with refund option');
      }

      $drSubscription = $this->drService->cancelSubscription($subscription->dr_subscription_id);
      DrLog::info(__FUNCTION__, 'dr-subscription cancelled', $subscription);

      $activeInvoice = $subscription->getActiveInvoice();

      if ($refund) {
        $subscription->stop(Subscription::STATUS_STOPPED, 'cancelled with refund');
        DrLog::info(__FUNCTION__, 'subscription active => stopped', $subscription);

        $subscription->user->updateSubscriptionLevel();
        DrLog::info(__FUNCTION__, 'user subscription level updated', $subscription);
      } else {
        $subscription->end_date =
          $drSubscription->getCurrentPeriodEndDate() ? Carbon::parse($drSubscription->getCurrentPeriodEndDate()) : null;
        $subscription->sub_status = Subscription::SUB_STATUS_CANCELLING;
        $subscription->next_invoice_date = null;
        $subscription->next_invoice = null;
        $subscription->active_invoice_id = null;
        $subscription->save();
        DrLog::info(__FUNCTION__, 'subscription active => cancelling', $subscription);
      }

      if ($activeInvoice) {
        $activeInvoice->setStatus(Invoice::STATUS_CANCELLED);
        $activeInvoice->save();
        DrLog::info(__FUNCTION__, 'update active invoice => cancelled', $activeInvoice);
      }

      // send notification
      if ($refund) {
        $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED_REFUND, $refund->invoice);
      } else {
        $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED);
      }
      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function cancelOrder(Invoice $invoice): Invoice
  {
    if ($invoice->period != 0 || $invoice->status != Invoice::STATUS_PENDING) {
      throw new Exception('Only the first order in pending status can be cancelled', 500);
    }

    $subscription = $invoice->subscription;
    if ($subscription->status == Subscription::STATUS_PROCESSING) {
      throw new Exception('Subscription is processing, can not cancel order', 409);
    }

    try {
      $this->drService->fulfillOrder($invoice->getDrOrderId(), null, true);
      DrLog::info(__FUNCTION__, 'dr-order cancelled', $subscription);

      $invoice->setStatus(Invoice::STATUS_CANCELLED);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'dr-order pending => cancelled', $invoice);

      $subscription->stop(Subscription::STATUS_FAILED, 'manually cancelled');
      $subscription->save();
      DrLog::info(__FUNCTION__, 'subscription pending => failed', $subscription);

      // send notification
      $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_CANCELLED, $invoice);
      return $invoice;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function createRefund(Invoice $invoice, float $amount = 0, string $reason = null): Refund
  {
    $result = RefundRules::invoiceRefundable($invoice);
    if (!$result['refundable']) {
      throw new Exception('try to refund non-refundable invoice', 500);
    }

    $refund = Refund::newFromInvoice($invoice, $amount, $reason);
    $drRefund = $this->drService->createRefund($refund);
    DrLog::info(__FUNCTION__, 'create dr refund', $invoice);

    $refund->setDrRefundId($drRefund->getId());
    $refund->save();
    DrLog::info(__FUNCTION__, 'create refund', $invoice);

    $invoice->setStatus(Invoice::STATUS_REFUNDING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'update invoice status => refunding', $invoice);

    return $refund;

    // note: notification will be sent from event handler
  }

  /**
   * customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo)
  {
    $user = $billingInfo->user;
    if (empty($user->dr['customer_id'])) {
      $customer = $this->drService->createCustomer($billingInfo);
      DrLog::info(__FUNCTION__, 'dr-customer created', $user);

      $user->dr = ['customer_id' => $customer->getId()];
      $user->save();
      DrLog::info(__FUNCTION__, 'user updated: dr.customer_id', $user);
    } else {
      $customer = $this->drService->updateCustomer($user->dr['customer_id'], $billingInfo);
      DrLog::info(__FUNCTION__, 'dr-customer updated', $user);
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

    if ($previousSourceId == $sourceId) {
      return $paymentMethod;
    }

    // attach source to customer
    $source = $this->drService->attachCustomerSource($user->dr['customer_id'], $sourceId);
    DrLog::info(__FUNCTION__, 'dr-source attached to dr-customer', $user);

    // detach previous source
    if ($previousSourceId) {
      $this->drService->detachCustomerSource($user->dr['customer_id'], $previousSourceId);
      DrLog::info(__FUNCTION__, 'old dr-source detached from dr-customer', $user);
    }

    // attach source to active subscription
    $subscription = $user->getActiveLiveSubscription();
    if ($subscription) {
      $this->drService->updateSubscriptionSource($subscription->dr_subscription_id, $source->getId());
      DrLog::info(__FUNCTION__, 'dr-source attached to dr-subscription', $subscription);
    }

    $paymentMethod = $paymentMethod ?: new PaymentMethod(['user_id' => $user->id]);

    $__FUNCTION__ = __FUNCTION__;
    DB::transaction(function () use ($user, $paymentMethod, $source, $subscription, $__FUNCTION__) {
      // create / update payment method
      $paymentMethod->type = $source->getType();
      $paymentMethod->dr = ['source_id' => $source->getId()];
      if ($source->getType() == 'creditCard') {
        $paymentMethod->display_data = [
          'brand'             => $source->getCreditCard()->getBrand(),
          'last_four_digits'  => $source->getCreditCard()->getLastFourDigits(),
          'expiration_year'   => $source->getCreditCard()->getExpirationYear(),
          'expiration_month'  => $source->getCreditCard()->getExpirationMonth(),
        ];
      } else if ($source->getType() == 'googlePay') {
        $paymentMethod->display_data = [
          'brand'             => $source->getGooglePay()->getBrand(),
          'last_four_digits'  => $source->getGooglePay()->getLastFourDigits(),
          'expiration_year'   => $source->getGooglePay()->getExpirationYear(),
          'expiration_month'  => $source->getGooglePay()->getExpirationMonth(),
        ];
      } else {
        $paymentMethod->display_data = null;
      }

      $paymentMethod->save();
      DrLog::info($__FUNCTION__, 'payment-method updated', $user);

      // update active subscription
      if ($subscription) {
        $subscription->setDrSourceId($source->getId());
        $subscription->save();
        DrLog::info($__FUNCTION__, 'subscription updated: dr.source_id', $subscription);
      }
    });

    return $paymentMethod;
  }

  /**
   * Tax Id
   */

  public function createTaxId(User $user, string $type, string $value): TaxId
  {
    /** @var TaxId|null $taxId */
    $taxId = TaxId::where('user_id', $user->id)->where('type', $type)->first();

    // already exists
    if ($taxId && $taxId->value == $value) {
      return $taxId;
    }

    // create new taxId
    $drTaxId = $this->drService->createTaxId($type, $value);
    DrLog::info(__FUNCTION__, 'dr-taxId created', $user);

    if ($drTaxId->getState() == DrCustomerTaxIdentifier::STATE_NOT_VALID || !isset($drTaxId->getApplicability()[0])) {
      $this->drService->deleteTaxId($drTaxId->getId());
      throw new \Exception('tax id is invalid', 400);
    }

    if ($taxId) {
      // delete old taxId
      $this->drService->deleteTaxId($taxId->dr_tax_id);
      DrLog::info(__FUNCTION__, 'remove dr-taxId of same type', $user);
    }

    $this->drService->attachCustomerTaxId($user->dr['customer_id'], $drTaxId->getId());
    DrLog::info(__FUNCTION__, 'dr-taxId attach to customer', $user);

    $taxId = $taxId ?? new TaxId();
    $taxId->customer_type = "";

    $taxId->user_id = $user->id;
    $taxId->dr_tax_id = $drTaxId->getId();
    $taxId->country = $drTaxId->getApplicability()[0]->getCountry();
    foreach ($drTaxId->getApplicability() as $applicability) {
      if (!$taxId->customer_type) {
        $taxId->customer_type = $applicability->getCustomerType();
        continue;
      }
      if ($applicability->getCustomerType() != $taxId->customer_type) {
        $taxId->customer_type = TaxId::CUSTOMER_TYPE_INDIVIDUAL_BUSINESS;
      }
    }
    $taxId->type = $type;
    $taxId->value = $value;
    $taxId->status = $drTaxId->getState();
    $taxId->save();
    DrLog::info(__FUNCTION__, 'create/update taxId created', $user);

    return $taxId;
  }

  public function deleteTaxId(TaxId $taxId)
  {
    // TODO: need check whether tax id is in use?   

    // delete old taxId
    $this->drService->deleteTaxId($taxId->dr_tax_id);
    DrLog::info(__FUNCTION__, 'delete dr tax id', ['user_id' => $taxId->user_id]);

    $result = $taxId->delete();
    DrLog::info(__FUNCTION__, 'delete taxId', ['user_id' => $taxId->user_id]);

    return $result;
  }

  /**
   * webhook event
   */
  public function updateDefaultWebhook(bool $enable)
  {
    $this->drService->updateDefaultWebhook(array_keys($this->eventHandlers), $enable);
  }

  public function webhookHandler(array $event): \Illuminate\Http\JsonResponse
  {
    $eventInfo = [
      'type' => $event['type'],
      'action' => 'received',
      'id' => $event['id'],
      'subscription_id' => null,
    ];

    $eventHandler = $this->eventHandlers[$event['type']] ?? null;

    // no handler
    if (!$eventHandler || !method_exists($this, $eventHandler['handler'])) {
      $event['action'] = 'no-handler';
      DrLog::error(__FUNCTION__, 'event ignored: no-handler', $eventInfo);
      return response()->json($eventInfo);
    }

    // duplicated
    if (DrEvent::exists($event['id'])) {
      $eventInfo['action'] = 'duplicated';
      DrLog::warning(__FUNCTION__, 'event ignored: duplicated', $eventInfo);
      return response()->json($eventInfo);
    }

    try {
      DrLog::info(__FUNCTION__, 'event accepted: processing', $eventInfo);
      $object = DrObjectSerializer::deserialize($event['data']['object'], $eventHandler['class']);
      $handler = $eventHandler['handler'];
      $subscription = $this->$handler($object);
      $eventInfo['action'] = $subscription ? 'processed' : 'skipped';
      $eventInfo['subscription_id'] = ($subscription instanceof Subscription) ?  $subscription->id : null;
      DrLog::info(__FUNCTION__, 'event processed: ' . $eventInfo['action'], $eventInfo);
      DrEvent::log($eventInfo);
      return response()->json($eventInfo);
    } catch (\Throwable $th) {
      Log::error($th);
      $eventInfo['action'] = 'error';
      DrLog::error(__FUNCTION__, 'event processed: failed', $eventInfo);
      return response()->json($eventInfo, 400);
    }
  }

  /**
   * trigger event
   */
  public function triggerEvent(string $eventId)
  {
    $drEvent = $this->drService->eventApi->retrieveEvents($eventId);
    $event = (array)DrObjectSerializer::sanitizeForSerialization($drEvent);
    $event['data'] = (array)$event['data'];
    $result = $this->webhookHandler($event);
    return $result;
  }

  /**
   * order event handlers
   */
  protected function validateOrder(DrOrder $order, array $options = ['be_first' => true], string $__FUNCTION__ = __FUNCTION__): Subscription|null
  {
    // must be a subscription order
    $drSubscriptionId = $order->getItems()[0]->getSubscriptionInfo()?->getSubscriptionId();
    if (!$drSubscriptionId) {
      DrLog::warning($__FUNCTION__, 'order skipped: no valid dr-subscription', ['order_id' => $order->getId()]);
      return null;
    }

    // validate the subscription
    $subscription = Subscription::where('dr_subscription_id', $drSubscriptionId)->first();
    if (!$subscription) {
      DrLog::warning($__FUNCTION__, 'order skipped: no invalid subscription', ['order_id' => $order->getId()]);
      return null;
    }

    // only process the first order
    if ($options['be_first'] ?? false) {
      if (!isset($subscription->dr['order_id']) || $subscription->dr['order_id'] != $order->getId()) {
        DrLog::info($__FUNCTION__, 'order skipped: not the first one', $subscription);
        return null;
      }
    }

    return $subscription;
  }

  protected function onOrderAccepted(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // must be in pending status
    if ($subscription->status != Subscription::STATUS_PENDING) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: not in pending', $subscription);
      return null;
    }

    // fulfill order
    try {
      $this->drService->fulfillOrder($drOrder->getId(), $drOrder);
      DrLog::info(__FUNCTION__, 'dr-order fulfilled', $subscription);
    } catch (\Throwable $th) {
      $invoice->setStatus(Invoice::STATUS_FAILED);
      $invoice->save();

      $subscription->stop(Subscription::STATUS_FAILED, 'fulfill dr-order fails');
      DrLog::warning(__FUNCTION__, 'subscription stopped => failed: fulfillment failed', $subscription);

      $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
      return null;
    }

    $invoice->setStatus(Invoice::STATUS_PROCESSING);
    $invoice->save();

    // update subscription status
    $subscription->setStatus(Subscription::STATUS_PROCESSING);
    $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription updated => processing', $subscription);

    return $subscription;
  }

  protected function onOrderBlocked(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // must be in pending status
    if (
      $subscription->status != Subscription::STATUS_PENDING &&
      $subscription->status != Subscription::STATUS_PROCESSING
    ) {
      DrLog::warning(__FUNCTION__, 'subscription skipped, not in pending or processing status', $subscription);
      return null;
    }

    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();

    // update subscription status
    $subscription->stop(Subscription::STATUS_FAILED, 'first order being blocked');
    DrLog::info(__FUNCTION__, 'subscription stopped => failed', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
    return $subscription;
  }

  protected function onOrderCancelled(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: already in failed', $subscription);
      return null;
    }

    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();

    $subscription->stop(Subscription::STATUS_FAILED, 'first order being cancelled');
    DrLog::info(__FUNCTION__, 'subscription stopped => failed', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);

    return $subscription;
  }

  protected function onOrderChargeFailed(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      DrLog::info(__FUNCTION__, 'subscription skipped: already in failed', $subscription);
      return null;
    }

    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();

    $subscription->stop(Subscription::STATUS_FAILED, 'first order failed');
    DrLog::info(__FUNCTION__, 'subscription stopped', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
    return $subscription;
  }

  protected function onOrderChargeCaptureComplete(DrCharge $charge): Subscription|null
  {
    // validate the order
    $order = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($order, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    DrLog::info(__FUNCTION__, 'order charge capture completed', $subscription);

    return $subscription;
  }

  protected function onOrderChargeCaptureFailed(DrCharge $charge): Subscription|null
  {
    // validate the order
    $drOrder = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: already in failed', $subscription);
      return null;
    }

    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();

    $subscription->stop(Subscription::STATUS_FAILED, 'first order charge capture failed');
    DrLog::info(__FUNCTION__, 'subscription stopped => failed', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
    return $subscription;
  }

  protected function onOrderComplete(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    // must be in processing status
    if ($subscription->status != Subscription::STATUS_PROCESSING) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: not in processing', $subscription);
      return null;
    }

    // activate dr subscription
    $drSubscription = $this->drService->activateSubscription($subscription->dr_subscription_id);
    DrLog::info(__FUNCTION__, 'dr-subscription activated', $subscription);

    // cancel previous subscription
    if ($previousSubscription = $subscription->user->getActiveLiveSubscription()) {
      $this->cancelSubscription($previousSubscription);
    }

    $__FUNCTION__ = __FUNCTION__;
    DB::transaction(function () use ($drOrder, $subscription, $invoice, $drSubscription, $__FUNCTION__) {
      $user = $subscription->user;

      // stop previous subscription
      $previousSubscription = $user->getActiveSubscription();
      if ($previousSubscription) {
        $previousSubscription->stop(Subscription::STATUS_STOPPED, 'new subscrption activated');
      }

      // active current subscription
      $this->fillSubscriptionAmount($subscription, $drOrder);

      $subscription->start_date = now();
      $subscription->current_period = 1;
      $subscription->current_period_start_date = now();
      $subscription->current_period_end_date =
        $drSubscription->getCurrentPeriodEndDate() ? Carbon::parse($drSubscription->getCurrentPeriodEndDate()) : null;
      $subscription->next_invoice_date =
        $drSubscription->getNextInvoiceDate() ? Carbon::parse($drSubscription->getNextInvoiceDate()) : null;
      $subscription->setStatus(Subscription::STATUS_ACTIVE);
      $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
      $subscription->fillNextInvoice();
      $subscription->active_invoice_id = null;
      $subscription->save();
      DrLog::info($__FUNCTION__, 'subscription updated => active', $subscription);

      // update user subscription level
      $user->updateSubscriptionLevel();
      DrLog::info($__FUNCTION__, 'user subscription level updated', $subscription);

      // update invoice status
      $invoice->period              = $subscription->current_period;
      $invoice->period_start_date   = $subscription->current_period_start_date;
      $invoice->period_end_date     = $subscription->current_period_end_date;
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      $invoice->save();
      DrLog::info($__FUNCTION__, 'first invoice created', $subscription);
    });

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_CONFIRMED, $invoice);
    return $subscription;
  }

  protected function onOrderInvoiceCreated(array $orderInvoice): Invoice|null
  {
    // validate order
    $invoice = Invoice::findByDrOrderId($orderInvoice['orderId']);
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: no valid invoice', ['dr_order_id' => $orderInvoice['orderId']]);
      return null;
    }


    // skip duplicated invoice
    if ($invoice->pdf_file) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: pdf file already exists', $invoice);
      return null;
    }

    // create invoice pdf download link
    $fileLink = $this->drService->createFileLink($orderInvoice['fileId'], now()->addYear());
    DrLog::info(__FUNCTION__, 'pdf file link created', $invoice);

    // update invoice
    $invoice->pdf_file = $fileLink->getUrl();
    $invoice->setDrFileId($orderInvoice['fileId']);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice pdf file created', $invoice);

    // sent notification
    $invoice->subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_INVOICE, $invoice);
    return $invoice;
  }

  protected function onOrderCreditMemoCreated(array $orderCreditMemo): Invoice|null
  {
    // validate order
    $invoice = Invoice::findByDrOrderId($orderCreditMemo['orderId']);
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: no valid credit memo', ['dr_order_id' => $orderCreditMemo['orderId']]);
      return null;
    }

    // skip duplicated invoice
    if ($invoice->findCreditMemoByFileId($orderCreditMemo['fileId']) !== false) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: credit memo already exists', $invoice);
      return null;
    }

    // create credit memo download link
    $fileLink = $this->drService->createFileLink($orderCreditMemo['fileId'], now()->addYear());
    DrLog::info(__FUNCTION__, 'credit memo link created', $invoice);

    // update invoice
    $invoice->addCreditMemo($orderCreditMemo['fileId'], $fileLink->getUrl());
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => completed', $invoice);

    // sent notification
    $invoice->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
      $invoice,
      ['file_id' => $orderCreditMemo['fileId']]
    );
    return $invoice;
  }

  protected function onOrderChargeback(DrOrder $order): Subscription|null
  {
    $subscription = $this->validateOrder($order, [], __FUNCTION__: __FUNCTION__);

    if (
      $subscription->status == Subscription::STATUS_ACTIVE &&
      $subscription->sub_status != Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->cancelSubscription($subscription);
      DrLog::warning(__FUNCTION__, 'subscription cancelled', $subscription);
    }

    $user = $subscription->user;
    $user->type = User::TYPE_BLACKLISTED;
    $user->save();
    DrLog::warning(__FUNCTION__, 'user blacklisted', $subscription);

    return $subscription;
  }

  protected function onOrderRefunded(DrOrder $order): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($order->getId());
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: no valid invoice', ['dr_order_id' => $order->getId()]);
      return null;
    }

    if ($invoice->status != Invoice::STATUS_REFUNDED) {
      $invoice->total_refunded = $order->getRefundedAmount();
      $invoice->setStatus($order->getAvailableToRefundAmount() > 0 ?
        Invoice::STATUS_PARTLY_REFUNDED :
        Invoice::STATUS_REFUNDED);
      $invoice->save();

      $invoice->subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_REFUNDED, $invoice);
    }

    return $invoice;
  }

  protected function createFirstInvoice(Subscription $subscription): Invoice
  {
    $invoice = new Invoice();

    $invoice->user_id             = $subscription->user_id;
    $invoice->subscription_id     = $subscription->id;
    $invoice->currency            = $subscription->currency;

    $invoice->period              = $subscription->current_period;
    $invoice->period_start_date   = $subscription->current_period_start_date;
    $invoice->period_end_date     = $subscription->current_period_end_date;

    $invoice->billing_info        = $subscription->billing_info;
    $invoice->tax_id_info         = $subscription->tax_id_info;
    $invoice->plan_info           = $subscription->plan_info;
    $invoice->coupon_info         = $subscription->coupon_info;

    $invoice->setDrOrderId(null);
    $invoice->setStatus(Invoice::STATUS_INIT);
    $invoice->save();

    $subscription->active_invoice_id = $invoice->id;
    $subscription->save();

    return $invoice;
  }

  protected function createRenewInvoice(Subscription $subscription, DrInvoice $drInvoice): Invoice
  {
    if ($subscription->invoices()->where('period', $subscription->current_period + 1)->count()) {
      throw new Exception('Try to create duplicated invoice', 500);
    }

    $invoice = new Invoice();
    $invoice->user_id             = $subscription->user_id;
    $invoice->subscription_id     = $subscription->id;
    $invoice->currency            = $subscription->currency;

    $invoice->period              = $subscription->current_period + 1;
    $invoice->period_start_date   = $subscription->next_invoice['current_period_start_date'];
    $invoice->period_end_date     = $subscription->next_invoice['current_period_end_date'];

    $invoice->billing_info        = $subscription->billing_info;
    $invoice->tax_id_info         = $subscription->tax_id_info;
    $invoice->plan_info           = $subscription->next_invoice['plan_info'];
    $invoice->coupon_info         = $subscription->next_invoice['coupon_info'];

    $invoice->payment_method_info = $subscription->user->payment_method->info();

    $invoice->subtotal            = $drInvoice->getSubtotal();
    $invoice->total_tax           = $drInvoice->getTotalTax();
    $invoice->total_amount        = $drInvoice->getTotalAmount();
    $invoice->invoice_date        = Carbon::parse($drInvoice->getStateTransitions()?->getOpen() ?? $drInvoice->getUpdatedTime());
    $invoice->setDrInvoiceId($drInvoice->getId());
    $invoice->setDrOrderId($drInvoice->getOrderId() ?? "");
    $invoice->setStatus(Invoice::STATUS_PENDING);
    $invoice->save();

    $subscription->active_invoice_id = $invoice->id;
    $subscription->save();

    return $invoice;
  }


  /**
   * subscription event handlers
   */

  protected function validateSubscription(DrSubscription $drSubscription, string $__FUNCTION__ = __FUNCTION__): Subscription|null
  {
    // validate the subscription
    $subscription = Subscription::where('dr_subscription_id', $drSubscription->getId())->first();
    if (!$subscription) {
      DrLog::warning($__FUNCTION__, 'subscription skipped: no valid subscription ', ['dr_subscription_id' => $drSubscription->getId()]);
      return null;
    }

    return $subscription;
  }

  protected function onSubscriptionExtended(array $event): Subscription|null
  {
    /** @var DrSubscription $drSubscription */
    $drSubscription = DrObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    /** @var DrInvoice $drInvoice */
    $drInvoice = DrObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: no active', $subscription);
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if (!$invoice) {
      $invoice = $this->createRenewInvoice($subscription, $drInvoice);
      DrLog::info(__FUNCTION__, 'renew invoice created', $subscription);
    }

    // update DrOrder
    try {
      $this->drService->updateOrderUpstreamId($drInvoice->getOrderId(), $invoice->id);
      DrLog::info(__FUNCTION__, 'update dr order upstream id', $invoice);
    } catch (\Throwable $th) {
      DrLog::error(__FUNCTION__, 'update dr order upstream id failed', $invoice);
      throw $th;
    }

    $__FUNCTION__ = __FUNCTION__;
    DB::transaction(function () use ($subscription, $drSubscription, $invoice, $drInvoice, $__FUNCTION__) {
      // update subscription data
      $this->fillSubscriptionAmount($subscription, $drInvoice);
      $subscription->current_period = $subscription->current_period + 1;
      $subscription->current_period_start_date = $subscription->current_period_end_date;
      $subscription->current_period_end_date = Carbon::parse($drSubscription->getCurrentPeriodEndDate());
      $subscription->next_invoice_date = Carbon::parse($drSubscription->getNextInvoiceDate());

      $subscription->plan_info = $subscription->next_invoice['plan_info'];
      $subscription->coupon_info = $subscription->next_invoice['coupon_info'];

      $subscription->fillNextInvoice();

      // if in some abnormal situation, this event comes after cancell subscripton operation
      if ($subscription->sub_status == Subscription::SUB_STATUS_CANCELLING) {
        $subscription->next_invoice_date = null;
        $subscription->next_invoice = null;
      } else {
        $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
      }
      $subscription->save();
      DrLog::info($__FUNCTION__, 'subscription extended', $subscription);

      // update invoice
      $invoice->payment_method_info = $subscription->user->payment_method->info();
      $invoice->setDrOrderId($drInvoice->getOrderId());
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      $invoice->save();
      DrLog::info($__FUNCTION__, 'invoice updated => completed', $subscription);
    });

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_EXTENDED, $invoice);
    return $subscription;
  }

  protected function onSubscriptionFailed(DrSubscription $drSubscription): Subscription|null
  {
    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = $subscription->getActiveInvoice();

    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: not in active', ['subscription_id' => $drSubscription->getId()]);
      return null;
    }

    // stop subscription data
    $subscription->stop(Subscription::STATUS_FAILED, 'renew failed');
    DrLog::info(__FUNCTION__, 'subscription stoped => failed', $subscription);

    // update user subscription level
    $subscription->user->updateSubscriptionLevel();
    DrLog::info(__FUNCTION__, 'user subscription level updated', $subscription);

    // stop invoice
    if ($invoice) {
      $invoice->payment_method_info = $subscription->user->payment_method->info();
      $invoice->setStatus(Invoice::STATUS_FAILED);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice updated => failed', $subscription);
    }

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_FAILED, $invoice);
    return $subscription;
  }

  protected function onSubscriptionPaymentFailed(array $event): Subscription|null
  {
    /** @var DRSubscription $drSubscription */
    $drSubscription = DrObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    /** @var DRInvoice $drInvoice */
    $drInvoice = DrObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if (
      $subscription->status != Subscription::STATUS_ACTIVE
      || $subscription->sub_status == Subscription::SUB_STATUS_CANCELLING
    ) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: not in active or in cancelling status', $subscription);
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if ($invoice && $invoice->status == Invoice::STATUS_PENDING) {
      DrLog::info(__FUNCTION__, 'subscription skipped: invoice in pending already', $subscription);
      return $subscription;
    }

    if (!$invoice) {
      $invoice = $this->createRenewInvoice($subscription, $drInvoice);
      DrLog::info(__FUNCTION__, 'renew invoice created', $subscription);
    }

    $invoice->setStatus(Invoice::STATUS_PENDING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => pending', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_INVOICE_PENDING, $invoice);
    return $subscription;
  }

  protected function onSubscriptionReminder(array $event): Subscription|null
  {
    /** @var DRSubscription $drSubscription */
    $drSubscription = DrObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    // $drInvoice = DrObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if (
      $subscription->status != Subscription::STATUS_ACTIVE ||
      $subscription->sub_status == Subscription::SUB_STATUS_CANCELLING
    ) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: not in active or in cancelling status', $subscription);
      return null;
    }
    DrLog::info(__FUNCTION__, 'subscription renew reminded', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);
    return $subscription;
  }

  protected function onTaxIdStateChange(DrTaxId $drTaxId): TaxId|null
  {
    //
    $id = $drTaxId->getId();

    /** @var TaxId|null @taxId */
    $taxId = TaxId::where('dr_tax_id', $id)->first();
    if (!$taxId) {
      return null;
    }

    if ($taxId->status != $drTaxId->getState()) {
      $taxId->status = $drTaxId->getState();
      $taxId->save();
      DrLog::info(__FUNCTION__, 'tax id status updated', ['tax_id' => $taxId->id, 'status' => $taxId->status]);
    }

    return $taxId;
  }

  protected function onRefundPending(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if (!$refund) {
      return null;
    }

    if ($refund->status != Refund::STATUS_PENDING) {
      $refund->status = Refund::STATUS_PENDING;
      $refund->save();
      DrLog::info(__FUNCTION__, 'refund status updated => pending', ['refund_id' => $refund->id]);

      $invoice = $refund->invoice;
      $invoice->setStatus(Invoice::STATUS_REFUNDING);
      $invoice->save();

      DrLog::info(__FUNCTION__, 'invoice status updated => refunding', $invoice);
    }

    // TODO: send notification here
    return $refund;
  }

  protected function onRefundFailed(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if (!$refund) {
      return null;
    }

    if ($refund->status != Refund::STATUS_FAILED) {
      $refund->status = Refund::STATUS_FAILED;
      $refund->save();
      DrLog::info(__FUNCTION__, 'refund status updated => failed', ['refund_id' => $refund->id]);

      $invoice = $refund->invoice;
      $invoice->setStatus(Invoice::STATUS_REFUND_FAILED);
      $invoice->save();

      DrLog::info(__FUNCTION__, 'refund status updated => refund-failed', $invoice);
    }

    $refund->invoice->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,
      $refund->invoice,
      ['refund' => $refund]
    );
    return $refund;
  }

  protected function onRefundComplete(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if (!$refund) {
      return null;
    }

    if ($refund->status != Refund::STATUS_COMPLETED) {
      $refund->status = Refund::STATUS_COMPLETED;
      $refund->save();
      DrLog::info(__FUNCTION__, 'refund status updated => complete', ['refund_id' => $refund->id]);

      // invoice is not updated here, but in onOrderRefunded()
    }

    return $refund;
  }


  /*
  TODO: check data integrity

  x - subscriptions in pending or processing status stay for a long period of time
  x - there is open activity (one routing not completed)
  x - order.accepted is missing                 => stay in pending (> 30 minutes)
  x - order.blocked is missing                  => stay in pending (> 30 minutes)
  x - order.cancelled is missing                => stay in pending (> 30 minutes)
  x - order.charge.failed is missing            => stay in pending (> 30 minutes)
  x - order.charge.capture.complete is missing  => ok
  x - order.charge.capture.failed is missing    => staying in processing (> 30 minutes) -> checkout order state -> ???
  x - order.complete is missing                 => staying in processing (> 30 minutes) -> checkout order state -> complete
    - order.chargeback is missing               => ... => periodicall check event.type
    - subscription.extended is missing          => period_end expired
    - subscription.failed is missing            => period_end expired
    - subscription.payment_failed is missing    => ok => notification is missing
    - subscription.reminder is missing          => ok => notification is missing
    - invoice.open is missing                   => invoice is not created
    - order.invoice.created is missing          => check invoice that is in completed state
  */

  // TODO: refactor validateOrder()
}
