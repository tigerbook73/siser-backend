<?php

namespace App\Services\DigitalRiver;

use App\Events\SubscriptionOrderEvent;
use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\DrEventRecord;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionRenewal;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\RefundRules;
use Carbon\Carbon;
use Closure;
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
  static public function log(string $level, string $location, string $action, Subscription|Invoice|User|Refund|array $context = [])
  {
    if ($context instanceof Subscription) {
      $context = [
        'subscription_id' => $context->id,
        'subscription_status' => $context->status
      ];
    } else if ($context instanceof Invoice) {
      $context = [
        'subscription_id' => $context->subscription_id,
        'invoice_id' => $context->id,
        'invoice_status' => $context->status
      ];
    } else if ($context instanceof Refund) {
      $context = [
        'subscription_id' => $context->subscription_id,
        'invoice_id' => $context->invoice_id,
        'refund_id' => $context->id,
        'refund_status' => $context->status
      ];
    } else if ($context instanceof User) {
      $context = [
        'user_id' => $context->id,
        'subscription_level' => $context->subscription_level
      ];
    }
    Log::log($level, 'DR_LOG: ' . $location . ': ' . $action . ($context ? ':' : ''), $context);
  }

  static public function info(string $location, string $action, Subscription|Invoice|User|array $context = [])
  {
    // level == __FUNCTION__, same the other functions
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

class WebhookException extends Exception
{
  public function __construct(string $message, int $code = 599, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
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
      'order.chargeback'              => ['class' => 'array',               'handler' => 'onOrderChargeback'],
      'order.refunded'                => ['class' => DrOrder::class,        'handler' => 'onOrderRefunded'],
      'order.dispute'                 => ['class' => DrOrder::class,        'handler' => 'onOrderDispute'],
      'order.dispute.resolved'        => ['class' => DrOrder::class,        'handler' => 'onOrderDisputeResolved'],

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

  /**
   * wait for condition become true
   */
  protected function wait(Closure $check, Closure $postCheck = null, string $location = __FUNCTION__, string $message = 'condition', int $maxWait = 5, int $interval = 200_000)
  {
    $wait = 0;
    while ($wait < $maxWait) {
      if ($check()) {
        if ($wait > 0) {
          DrLog::info($location, "wait $wait intervals for '$message' and success");
        }
        return;
      }

      usleep($interval);
      $wait++;

      if ($postCheck) {
        $postCheck();
      }
    }

    DrLog::info($location, "wait $wait intervals for '$message' and failed");
    throw new WebhookException("wait for '$message' failed", 500);
  }

  public function createSubscription(User $user, Plan $plan, Coupon|null $coupon = null, TaxId|null $taxId = null): Subscription
  {
    // create subscription
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($user->billing_info)
      ->fillPlanAndCoupon($plan, $coupon)
      ->fillTaxId($taxId);
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
    $subscription
      ->fillAmountFromDrObject($checkout)
      ->setDrCheckoutId($checkout->getId())
      ->setDrSubscriptionId($checkout->getItems()[0]->getSubscriptionInfo()->getSubscriptionId())
      ->setDrSessionId($checkout->getPayment()->getSession()->getId())
      ->save();
    DrLog::info(__FUNCTION__, 'subscription updated: amounts & dr', $subscription);

    // update invoice
    $invoice->fillFromDrObject($checkout)->save();
    DrLog::info(__FUNCTION__, 'invoice updated: amounts & payment', $invoice);

    return $subscription;
  }

  public function retrieveTaxRate(User $user, TaxId|null $taxId = null): float
  {
    // create tax pre-calculate checkout
    try {
      $taxRate = $this->drService->retrieveTaxRate($user, $taxId);
      DrLog::info(__FUNCTION__, "tax rate retrieved: $taxRate");

      return $taxRate;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function deleteSubscription(Subscription $subscription): bool
  {
    if ($subscription->status != Subscription::STATUS_DRAFT) {
      throw new Exception('Try to delete subscription not in draft status', 500);
    }

    try {
      if ($subscription->getDrCheckoutId()) {
        $this->drService->deleteCheckout($subscription->getDrCheckoutId());
        DrLog::info(__FUNCTION__, 'dr-checkout deleted', $subscription);
      }
      if (isset($subscription->dr_subscription_id)) {
        $this->drService->deleteSubscription($subscription->dr_subscription_id);
        DrLog::info(__FUNCTION__, 'dr-subscription deleted', $subscription);
      }
    } catch (\Throwable $th) {
    }

    $invoice = $subscription->getActiveInvoice();
    $invoice?->delete();
    $subscription->delete();
    DrLog::info(__FUNCTION__, 'subscription and invoice deleted', $subscription);

    return true;
  }

  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string $terms = null): Subscription
  {
    try {
      // attach source_id to checkout
      $this->drService->attachCheckoutSource($subscription->getDrCheckoutId(), $paymentMethod->getDrSourceId());
      DrLog::info(__FUNCTION__, 'dr-source attached to dr-checkout', $subscription);

      // update checkout terms
      if ($terms) {
        $this->drService->updateCheckoutTerms($subscription->getDrCheckoutId(), $terms);
        DrLog::info(__FUNCTION__, 'dr-checkout terms update', $subscription);
      }

      // convert checkout to order
      $drOrder = $this->drService->convertCheckoutToOrder($subscription->getDrCheckoutId());
      DrLog::info(__FUNCTION__, 'dr-checkout converted to dr-order', $subscription);

      // update invoice
      $invoice = $subscription->getActiveInvoice();
      $invoice->fillFromDrObject($drOrder);
      $invoice->invoice_date = now();
      $invoice->setDrOrderId($drOrder->getId());
      $invoice->setStatus(Invoice::STATUS_PENDING);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice init => pending', $invoice);

      // update subscription
      $subscription->fillPaymentMethod($paymentMethod);
      $subscription->fillAmountFromDrObject($drOrder);
      $subscription->setDrOrderId($drOrder->getId());
      $subscription->setStatus(Subscription::STATUS_PENDING);
      $subscription->sub_status = ($drOrder->getState() == 'accepted') ? Subscription::SUB_STATUS_NORMAL : Subscription::SUB_STATUS_ORDER_PENDING;
      $subscription->save();
      DrLog::info(__FUNCTION__, "subscription updated => pending ({$subscription->sub_status})", $subscription);

      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function cancelSubscription(Subscription $subscription, bool $needRefund = false, bool $immediate = false): Subscription
  {
    try {
      $invoiceToRefund = null;
      if ($needRefund) {
        $result = RefundRules::customerRefundable($subscription);
        if (!$result['refundable']) {
          throw new Exception('try to refund non-refundable invoice', 500);
        }

        /** @var Invoice $invoiceToRefund */
        $invoiceToRefund = $result['invoice'];
        if ($invoiceToRefund->status == Invoice::STATUS_PROCESSING) {
          // refund later
          $invoiceToRefund->setSubStatus(Invoice::SUB_STATUS_TO_REFUND);
          $invoiceToRefund->save();
          DrLog::info(__FUNCTION__, 'update invoice sub-status => to-refund', $invoiceToRefund);
        } else {
          // refund immediately
          $this->createRefund($invoiceToRefund, reason: 'cancel subscription with refund option');
        }
      }

      $drSubscription = $this->drService->cancelSubscription($subscription->dr_subscription_id);
      DrLog::info(__FUNCTION__, 'dr-subscription cancelled', $subscription);

      $activeInvoice = $subscription->getActiveInvoice();
      if ($activeInvoice && $activeInvoice->id != $invoiceToRefund?->id) {
        $activeInvoice->setStatus(Invoice::STATUS_CANCELLED);
        $activeInvoice->save();
        DrLog::info(__FUNCTION__, 'update active invoice => cancelled', $activeInvoice);
      }

      if ($invoiceToRefund || $subscription->isFreeTrial()) {
        $subscription->stop(Subscription::STATUS_STOPPED, $invoiceToRefund ? 'cancelled with refund' : 'cancell during free trial');
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

        $subscription->cancelPendingOrActiveRenewal();
      }

      // log subscription event (stopped event is logged in stop())
      SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_CANCELLED, $subscription);

      // send notification
      if ($subscription->renewal_info && $subscription->renewal_info['status'] == SubscriptionRenewal::STATUS_EXPIRED) {
        $subscription->sendNotification(SubscriptionNotification::NOTIF_RENEW_EXPIRED);
      } else if ($invoiceToRefund) {
        $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED_REFUND, $invoiceToRefund);
      } else {
        $subscription->sendNotification(SubscriptionNotification::NOTIF_CANCELLED);
      }
      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function renewSubscription(Subscription $subscription): Subscription
  {
    if (!$subscription->renewal_info || $subscription->renewal_info['status'] !== SubscriptionRenewal::STATUS_ACTIVE) {
      throw new Exception('subscription has no active renewal', 500);
    }

    // complete renewal
    $subscription->completeActiveRenewal();

    // send confirmation notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED);

    // re-send reminder notification if it has been remindered but not starting to invoice
    $invoice = $subscription->getActiveInvoice();
    if ($invoice && $invoice->status == Invoice::STATUS_INIT) {
      // reminder event has been triggered, but not starting to charge
      $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);
    }

    return $subscription;
  }

  public function cancelOrder(Invoice $invoice): Invoice
  {
    if ($invoice->period != 0 || $invoice->status != Invoice::STATUS_PENDING) {
      throw new Exception('Only the first order in pending status can be cancelled', 500);
    }

    $subscription = $invoice->subscription;

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
    // adjust amount
    if ($amount == 0) {
      $amount = $invoice->total_amount - $invoice->total_refunded;
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

  public function createRefundFromDrObject(DrOrderRefund $drRefund): Refund|null
  {
    // if created from createRefund() or already created, skip
    if ($drRefund->getMetadata()) {
      return null;
    }

    if ($refund = Refund::findByDrRefundId($drRefund->getId())) {
      return $refund;
    }

    $invoice = Invoice::findByDrOrderId($drRefund->getOrderId());
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invalid dr-refund: no invoice found', ['dr_refund_id' => $drRefund->getId()]);
      return null;
    }

    $refund = Refund::newFromInvoice($invoice, $drRefund->getAmount(), $drRefund->getReason());
    $refund->setDrRefundId($drRefund->getId());
    $refund->save();
    DrLog::info(__FUNCTION__, 'create refund from dr-refund', $invoice);

    $invoice->setStatus(Invoice::STATUS_REFUNDING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'update invoice status => refunding', $invoice);

    return $refund;
  }

  /**
   * customer
   */
  public function createOrUpdateCustomer(BillingInfo $billingInfo)
  {
    $user = $billingInfo->user;
    if (!$user->getDrCustomerId()) {
      $customer = $this->drService->createCustomer($billingInfo);
      DrLog::info(__FUNCTION__, 'dr-customer created', $user);

      $user->setDrCustomerId($customer->getId());
      $user->save();
      DrLog::info(__FUNCTION__, 'user updated: dr.customer_id', $user);
    } else {
      $customer = $this->drService->updateCustomer($user->getDrCustomerId(), $billingInfo);
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
    $previousSourceId = $paymentMethod ? $paymentMethod->getDrSourceId() : null;

    if ($previousSourceId == $sourceId) {
      return $paymentMethod;
    }

    // attach source to customer
    $source = $this->drService->attachCustomerSource($user->getDrCustomerId(), $sourceId);
    DrLog::info(__FUNCTION__, 'dr-source attached to dr-customer', $user);

    // detach previous source
    if ($previousSourceId) {
      $this->drService->detachCustomerSource($user->getDrCustomerId(), $previousSourceId);
      DrLog::info(__FUNCTION__, 'old dr-source detached from dr-customer', $user);

      // TODO: send notifcation to notify user payment method updated
    }

    // attach source to active subscription
    $subscription = $user->getActiveLiveSubscription();
    if ($subscription) {
      $this->drService->updateSubscriptionSource($subscription->dr_subscription_id, $source->getId());
      DrLog::info(__FUNCTION__, 'dr-source attached to dr-subscription', $subscription);
    }

    $paymentMethod = $paymentMethod ?: new PaymentMethod(['user_id' => $user->id]);

    $paymentMethod->fillFromDrObject($source);
    $paymentMethod->save();
    DrLog::info(__FUNCTION__, 'payment-method updated', $user);

    // update active subscription
    if ($subscription) {
      $subscription->fillPaymentMethod($paymentMethod);
      $subscription->save();
      DrLog::info(__FUNCTION__, 'subscription updated: payment_method', $subscription);
    }

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

    $this->drService->attachCustomerTaxId($user->getDrCustomerId(), $drTaxId->getId());
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
      'type'            => $event['type'],
      'action'          => 'received',
      'id'              => $event['id'],
      'subscription_id' => null,
    ];

    $eventHandler = $this->eventHandlers[$event['type']] ?? null;

    // no handler
    if (!$eventHandler || !method_exists($this, $eventHandler['handler'])) {
      $event['action'] = 'no-handler';
      DrLog::error(__FUNCTION__, "event {$event['type']} ignored: no-handler", $eventInfo);
      return response()->json($eventInfo);
    }

    $drEvent = DrEventRecord::startProcessing($event['id'], $event['type']);
    $eventInfo['action'] = $drEvent->action;

    if ($drEvent->action == DrEventRecord::ACTION_ERROR) {
      DrLog::warning(__FUNCTION__, "event {$drEvent->type} {$drEvent->action}: {$drEvent->error}", $eventInfo);
      return response()->json($eventInfo, 409);
    }

    if ($drEvent->action == DrEventRecord::ACTION_IGNORE) {
      DrLog::info(__FUNCTION__, "event {$drEvent->type} {$drEvent->action}: {$drEvent->error}", $eventInfo);
      return response()->json($eventInfo, 200);
    }

    try {
      DrLog::info(__FUNCTION__, "event {$drEvent->type} accepted: processing", $eventInfo);
      $object = DrObjectSerializer::deserialize($event['data']['object'], $eventHandler['class']);
      $handler = $eventHandler['handler'];
      $object = $this->$handler($object);
      $eventInfo['action'] = $object ? 'processed' : 'skipped';
      $eventInfo['subscription_id'] = ($object instanceof Subscription) ?  $object->id : $object?->subscription_id;
      DrLog::info(__FUNCTION__, "event {$drEvent->type} processed: " . $eventInfo['action'], $eventInfo);
      $drEvent->complete($eventInfo['subscription_id']);
      return response()->json($eventInfo);
    } catch (\Throwable $th) {
      $drEvent->fail();
      if ($th instanceof WebhookException) {
        Log::warning($th->getMessage());
        $eventInfo['action'] = 'error';
        DrLog::warning(__FUNCTION__, "event {$drEvent->type} processed: failed", $eventInfo);
      } else {
        Log::error($th);
        $eventInfo['action'] = 'error';
        DrLog::error(__FUNCTION__, "event {$drEvent->type} processed: failed", $eventInfo);
      }
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

    /** @var Subscription|null $subscription */
    $subscription = Subscription::where('dr_subscription_id', $drSubscriptionId)->first();
    if (!$subscription) {
      DrLog::warning($__FUNCTION__, 'order skipped: no invalid subscription', ['order_id' => $order->getId()]);
      return null;
    }

    // only process the first order
    if ($options['be_first'] ?? false) {
      if ($subscription->getDrCheckoutId() !== $order->getCheckoutId()) {
        DrLog::info($__FUNCTION__, 'order skipped: not the first one', $subscription);
        return null;
      }

      $this->wait(
        check: fn () => $subscription->getDrOrderId() == $order->getId(),
        postCheck: fn () => $subscription->refresh(),
        location: $__FUNCTION__,
        message: 'paymentSubscrition() to complete'
      );
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

    // activate dr subscription
    $drSubscription = $this->drService->activateSubscription($subscription->dr_subscription_id);
    DrLog::info(__FUNCTION__, 'dr-subscription activated', $subscription);

    // cancel previous subscription
    if ($previousSubscription = $subscription->user->getActiveLiveSubscription()) {
      $this->cancelSubscription($previousSubscription);
    }

    // stop previous subscription
    if ($previousSubscription = $subscription->user->getActiveSubscription()) {
      $previousSubscription->stop(Subscription::STATUS_STOPPED, 'new subscrption activated');
    }

    // active current subscription
    $subscription->fillAmountFromDrObject($drOrder);
    $subscription->fillPeriodFromDrObject($drSubscription);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
    $subscription->fillNextInvoice();
    $subscription->active_invoice_id = null;
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription updated => active', $subscription);

    // update dr subscription
    if ($subscription->isNextPlanDifferent()) {
      $this->drService->convertSubscriptionToNext($drSubscription, $subscription);
      DrLog::info(__FUNCTION__, 'dr-subscription converted to next', $subscription);
    }

    // update user subscription level
    $subscription->user->updateSubscriptionLevel();
    DrLog::info(__FUNCTION__, 'user subscription level updated', $subscription);

    // update invoice status
    $invoice->fillPeriod($subscription);
    $invoice->fillFromDrObject($drOrder);
    $invoice->setStatus(Invoice::STATUS_PROCESSING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => processing', $invoice);

    if ($subscription->coupon_info) {
      $subscription->coupon->setUsage($subscription->id)->save();
      DrLog::info(__FUNCTION__, "coupon usage updated: status = {$subscription->coupon->status}", $subscription);
    }

    // create renewal if required
    $subscription->createRenewal();

    // log event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_ACTIVATED, $subscription);

    // disptch event 
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED, $invoice);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_CONFIRMED, $invoice);
    return $subscription;
  }

  protected function onOrderBlocked(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = Invoice::findByDrOrderId($drOrder->getId());

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: already in failed', $subscription);
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
    $invoice = Invoice::findByDrOrderId($drOrder->getId());

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
    $invoice = Invoice::findByDrOrderId($drOrder->getId());

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

  protected function onOrderChargeCaptureComplete(DrCharge $charge): Invoice|null
  {
    // validate the order
    $drOrder = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = Invoice::findByDrOrderId($drOrder->getId());

    DrLog::info(__FUNCTION__, 'order charge capture completed', $invoice);
    return $invoice;
  }

  protected function onOrderChargeCaptureFailed(DrCharge $charge): Invoice|null
  {
    // validate the order
    $drOrder = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = Invoice::findByDrOrderId($drOrder->getId());

    DrLog::info(__FUNCTION__, 'order charge capture failed', $invoice);
    return $invoice;

    /*

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      DrLog::warning(__FUNCTION__, 'subscription skipped: already in failed', $subscription);
      return null;
    }

    if ($subscription->status == Subscription::STATUS_ACTIVE) {
      $this->drService->cancelSubscription($subscription->dr_subscription_id);
      DrLog::info(__FUNCTION__, 'cancel dr subscription', $subscription);
    }

    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => failed', $invoice);

    $subscription->stop(Subscription::STATUS_FAILED, 'first order charge capture failed');
    $subscription->user->updateSubscriptionLevel();
    DrLog::info(__FUNCTION__, 'subscription stopped => failed', $subscription);

    if ($subscription->coupon_info) {
      $subscription->coupon->releaseUsage($subscription->id);
      DrLog::info(__FUNCTION__, "coupon usage updated: status = {$subscription->coupon->status}", $subscription);
    }

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
    return $subscription;

    */
  }

  protected function onOrderComplete(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      return null;
    }

    // DR-BUG: $drOrder->getState() is not completed
    if ($drOrder->getState() !== DrOrder::STATE_COMPLETE) {
      throw new WebhookException("the state of dr-order in order.complete event is {$drOrder->getState()}.", 500);
    }

    // wait for onOrderAccept to complete
    $this->wait(
      check: fn () => $invoice->period > 0,
      postCheck: fn () => $invoice->refresh(),
      location: __FUNCTION__,
      message: 'onOrderAccept() to complete'
    );

    if ($invoice->status == Invoice::STATUS_COMPLETED) {
      DrLog::info(__FUNCTION__, 'invoice skipped: already in completed', $invoice);
      return null;
    }

    if ($invoice->sub_status == Invoice::SUB_STATUS_TO_REFUND) {
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      DrLog::info(__FUNCTION__, 'invoice updated => completed', $invoice);

      $this->createRefund($invoice, reason: 'refund when order completed');
      $invoice->save();
    } else {
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice updated => completed', $invoice);
    }

    return $invoice;
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
    $fileLink = $this->drService->createFileLink($orderInvoice['fileId'], now()->addYears(10));
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
      ['credit_memo' => $fileLink->getUrl()]
    );
    return $invoice;
  }

  protected function onOrderDispute(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: no valid invoice', ['dr_order_id' => $drOrder->getId()]);
      return null;
    }

    if (
      $invoice->getDisputeStatus() != Invoice::DISPUTE_STATUS_DISPUTING &&
      $invoice->getDisputeStatus() != Invoice::DISPUTE_STATUS_DISPUTED
    ) {
      $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_DISPUTING);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice disputing', $invoice);
    }

    return $invoice;
  }

  protected function onOrderDisputeResolved(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      DrLog::warning(__FUNCTION__, 'invoice skipped: no valid invoice', ['dr_order_id' => $drOrder->getId()]);
      return null;
    }

    if ($invoice->getDisputeStatus() == Invoice::DISPUTE_STATUS_DISPUTING) {
      $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_NONE);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice dispute resolved', $invoice);
    }

    return $invoice;
  }

  protected function onOrderChargeback(array $chargeback): Invoice|null
  {
    if ($chargeback['amount'] == 0) {
      // skip chargeback fee event
      return null;
    }

    $invoice = Invoice::findByDrOrderId($chargeback['orderId']);
    if (!$invoice) {
      DrLog::error(__FUNCTION__, 'invoice skipped: no valid invoice', ['dr_order_id' => $chargeback['orderId']]);
      return null;
    }

    $subscription = $invoice->subscription;
    if (
      $subscription->status == Subscription::STATUS_ACTIVE &&
      $subscription->sub_status != Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->cancelSubscription($subscription);
      DrLog::warning(__FUNCTION__, 'subscription cancelled', $subscription);
    }

    $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_DISPUTED);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice disputed', $invoice);

    $user = $subscription->user;
    $user->type = User::TYPE_BLACKLISTED;
    $user->save();
    DrLog::warning(__FUNCTION__, 'user blacklisted', $subscription);

    // invoice is chargebacked
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_REFUNDED, $invoice, null);

    return $invoice;
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
    $invoice->fillBasic($subscription)
      ->fillPeriod($subscription)
      ->setDrOrderId(null)
      ->setStatus(Invoice::STATUS_INIT)
      ->save();

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
    $invoice->fillBasic($subscription, true)
      ->fillPeriod($subscription, true)
      ->fillFromDrObject($drInvoice)
      ->setDrInvoiceId($drInvoice->getId())
      ->setDrOrderId($drInvoice->getOrderId())
      ->setStatus(Invoice::STATUS_INIT)
      ->save();

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

    if (($subscription->coupon_info['discount_type'] ?? null) == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      // log subscription event
      SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_CONVERTED, $subscription);
    }

    // if there is an open renewal, expires it (before move to next period)
    $subscription->expireActiveRenewal();

    // update subscription data
    $subscription->moveToNext();
    $subscription->fillAmountFromDrObject($drInvoice);
    $subscription->fillPeriodFromDrObject($drSubscription);
    $subscription->fillNextInvoice();

    // TODO: if in some abnormal situation, this event comes after cancell subscripton operation
    if ($subscription->sub_status == Subscription::SUB_STATUS_CANCELLING) {
      $subscription->next_invoice_date = null;
      $subscription->next_invoice = null;
    } else {
      $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
    }
    $subscription->active_invoice_id = null;
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription extended', $subscription);

    // update dr subscription
    if ($subscription->isNextPlanDifferent()) {
      $this->drService->convertSubscriptionToNext($drSubscription, $subscription);
      DrLog::info(__FUNCTION__, 'dr-subscription converted to next', $subscription);
    }

    // create renewal is required
    $subscription->createRenewal();

    // update invoice
    $invoice->fillFromDrObject($drInvoice);
    $invoice->setDrOrderId($drInvoice->getOrderId());
    $invoice->setStatus(Invoice::STATUS_PROCESSING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => completed', $subscription);

    // log subscription event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_EXTENDED, $subscription);

    // disptch event
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED, $invoice);

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
      $invoice->setStatus(Invoice::STATUS_FAILED);
      $invoice->save();
      DrLog::info(__FUNCTION__, 'invoice updated => failed', $subscription);
    }

    // log subscription event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_FAILED, $subscription);

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

    $invoice->fillFromDrObject($drInvoice);
    $invoice->setStatus(Invoice::STATUS_PENDING);
    $invoice->save();
    DrLog::info(__FUNCTION__, 'invoice updated => pending', $subscription);

    // update subscription
    $subscription->fillNextInvoiceAmountFromDrObject($drInvoice);
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription amount updated from dr object ', $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_INVOICE_PENDING, $invoice);
    return $subscription;
  }

  protected function onSubscriptionReminder(array $event): Subscription|null
  {
    /** @var DRSubscription $drSubscription */
    $drSubscription = DrObjectSerializer::deserialize($event['subscription'], DrSubscription::class);
    $drInvoice = DrObjectSerializer::deserialize($event['invoice'], DrInvoice::class);

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

    // create invoice
    $invoice = $this->createRenewInvoice($subscription, $drInvoice);
    DrLog::info(__FUNCTION__, 'renew invoice created', $subscription);

    // update subscription
    $subscription->fillNextInvoiceAmountFromDrObject($drInvoice);
    $subscription->save();
    DrLog::info(__FUNCTION__, 'subscription amount updated from dr object ', $subscription);

    // send notification
    if ($subscription->isRenewalPendingOrActive()) {
      $subscription->sendRenewNotification();
    } else {
      $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);
    }
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
    if ($refund) {
      return null;
    }

    // create refund is not exist
    return $this->createRefundFromDrObject($drRefund);
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

      // send refunded event
      SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_REFUNDED, $refund->invoice, $refund);

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

  /**
   * try function
   */

  public function tryCompleteInvoice(Invoice $invoice): bool
  {
    if ($invoice->getStatus() !== Invoice::STATUS_PROCESSING) {
      return false;
    }

    try {
      $drOrder = $this->drService->getOrder($invoice->getDrOrderId());
      if ($drOrder->getState() !== DrOrder::STATE_COMPLETE) {
        return false;
      }

      // complete order
      DrLog::info(__FUNCTION__, 'try to complete invoice by dr order\'s state.', $invoice);
      $this->onOrderComplete($drOrder);
      $invoice->refresh();
      DrLog::info(__FUNCTION__, 'complete invoice by dr order\'s state.', $invoice);

      return true;
    } catch (\Throwable $th) {
      // silient
    }

    return false;
  }
}
