<?php

namespace App\Services\DigitalRiver;

use App\Events\SubscriptionOrderEvent;
use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\DrEventRawRecord;
use App\Models\DrEventRecord;
use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\LicenseSharing;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\SubscriptionRenewal;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\LicenseSharing\LicenseSharingService;
use App\Services\RefundRules;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Closure;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CustomerTaxIdentifier as DrCustomerTaxIdentifier;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\SalesTransaction as DrSalesTransaction;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\TaxIdentifier as DrTaxId;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SubscriptionManagerDR implements SubscriptionManager
{
  public $eventHandlers = [];

  public function __construct(public DigitalRiverService $drService, public LicenseSharingService $licenseService, public SubscriptionManagerResult $result)
  {
    $this->eventHandlers = [
      // order events
      'order.accepted'                => ['class' => DrOrder::class,              'handler' => 'onOrderAccepted'],
      'order.blocked'                 => ['class' => DrOrder::class,              'handler' => 'onOrderBlocked'],
      'order.cancelled'               => ['class' => DrOrder::class,              'handler' => 'onOrderCancelled'],
      'order.charge.failed'           => ['class' => DrCharge::class,             'handler' => 'onOrderChargeFailed'],
      'order.charge.capture.complete' => ['class' => DrCharge::class,             'handler' => 'onOrderChargeCaptureComplete'],
      'order.charge.capture.failed'   => ['class' => DrCharge::class,             'handler' => 'onOrderChargeCaptureFailed'],
      'order.complete'                => ['class' => DrOrder::class,              'handler' => 'onOrderComplete'],
      'order.chargeback'              => ['class' => DrSalesTransaction::class,   'handler' => 'onOrderChargeback'],
      'order.refunded'                => ['class' => DrOrder::class,              'handler' => 'onOrderRefunded'],
      'order.dispute'                 => ['class' => DrOrder::class,              'handler' => 'onOrderDispute'],
      'order.dispute.resolved'        => ['class' => DrOrder::class,              'handler' => 'onOrderDisputeResolved'],

      // subscription events
      'subscription.extended'         => ['class' => 'array',                     'handler' => 'onSubscriptionExtended'],
      'subscription.failed'           => ['class' => DrSubscription::class,       'handler' => 'onSubscriptionFailed'],
      'subscription.lapsed'           => ['class' => DrSubscription::class,       'handler' => 'onSubscriptionLapsed'],
      'subscription.payment_failed'   => ['class' => 'array',                     'handler' => 'onSubscriptionPaymentFailed'],
      'subscription.reminder'         => ['class' => 'array',                     'handler' => 'onSubscriptionReminder'],
      'subscription.source_invalid'   => ['class' => DrSubscription::class,       'handler' => 'onSubscriptionSourceInvalid'],

      // invoice events: see Invoice.md for state machine
      'order.invoice.created'         => ['class' => 'array',                     'handler' => 'onOrderInvoiceCreated'],
      'order.credit_memo.created'     => ['class' => 'array',                     'handler' => 'onOrderCreditMemoCreated'],

      // refund events
      'refund.pending'                => ['class' => DrOrderRefund::class,        'handler' => 'onRefundPending'],
      'refund.failed'                 => ['class' => DrOrderRefund::class,        'handler' => 'onRefundFailed'],
      'refund.complete'               => ['class' => DrOrderRefund::class,        'handler' => 'onRefundComplete'],

      // tax id
      'tax_identifier.verified'       => ['class' => DrTaxId::class,              'handler' => 'onTaxIdStateChange'],
      'tax_identifier.not_valid'      => ['class' => DrTaxId::class,              'handler' => 'onTaxIdStateChange'],
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
          $this->result->appendMessage("wait $wait intervals for '$message' and success", location: $location);
        }
        return;
      }

      usleep($interval);
      $wait++;

      if ($postCheck) {
        $postCheck();
      }
    }

    throw new WebhookException("wait for '$message' failed", 500);
  }

  public function createSubscription(User $user, Plan $plan, Coupon $coupon = null, TaxId $taxId = null, LicensePackage $licensePackage = null, int $licenseQuantity = 0): Subscription
  {
    // create subscription
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($user->billing_info)
      ->fillPlanAndCoupon($plan, $coupon, $licensePackage, $licenseQuantity)
      ->fillTaxId($taxId);
    $subscription->save();
    $this->result->appendMessage("subscription ({$subscription->id}) created", location: __FUNCTION__);

    $invoice = $this->createFirstInvoice($subscription);
    $this->result->appendMessage("first invoice ({$invoice->id}) created", location: __FUNCTION__);

    // create checkout
    try {
      $checkout = $this->drService->createCheckout($subscription);
      $this->result->appendMessage('dr-checkout created', location: __FUNCTION__);
    } catch (\Throwable $th) {
      $invoice->delete();
      $subscription->delete();
      $this->result->appendMessage('subscription & invoice deleted when creating dr-checkout failed', location: __FUNCTION__);

      throw $th;
    }

    // update subscription
    $subscription
      ->fillAmountFromDrObject($checkout)
      ->setDrCheckoutId($checkout->getId())
      ->setDrSubscriptionId($checkout->getItems()[0]->getSubscriptionInfo()->getSubscriptionId())
      ->setDrSessionId($checkout->getPayment()->getSession()->getId())
      ->save();
    $this->result->appendMessage("subscription ({$subscription->id}) updated from drObject", location: __FUNCTION__);

    // update invoice
    $invoice->fillFromDrObject($checkout)->save();
    $this->result->appendMessage("invoice ({$invoice->id}) updated from drObject", location: __FUNCTION__);

    return $subscription;
  }

  public function retrieveTaxRate(User $user, TaxId|null $taxId = null): float
  {
    // create tax pre-calculate checkout
    try {
      $taxRate = $this->drService->retrieveTaxRate($user, $taxId);
      return $taxRate;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function deleteSubscription(Subscription $subscription): bool
  {
    if ($subscription->status != Subscription::STATUS_DRAFT) {
      throw new Exception("subscription ({$subscription->id}) not in draft status", 500);
    }

    try {
      if ($subscription->getDrCheckoutId()) {
        $this->drService->deleteCheckout($subscription->getDrCheckoutId());
        $this->result->appendMessage("dr-checkout for subscription ({$subscription->id}) deleted", location: __FUNCTION__);
      }
      if (isset($subscription->dr_subscription_id)) {
        $this->drService->deleteSubscription($subscription->dr_subscription_id);
        $this->result->appendMessage("dr-subscription for subscription ({$subscription->id}) deleted", location: __FUNCTION__);
      }
    } catch (\Throwable $th) {
      $this->result->appendMessage('delete dr-checkout or dr-subscription fails', location: __FUNCTION__);
    }

    $invoice = $subscription->getActiveInvoice();
    $invoice?->delete();
    $subscription->delete();
    $this->result->appendMessage('subscription and invoice deleted', location: __FUNCTION__);

    return true;
  }

  public function paySubscription(Subscription $subscription, PaymentMethod $paymentMethod, string $terms = null): Subscription
  {
    try {
      // attach source_id to checkout
      $this->drService->attachCheckoutSource($subscription->getDrCheckoutId(), $paymentMethod->getDrSourceId());
      $this->result->appendMessage("dr-source attached to dr-checkout for subscription ({$subscription->id})", location: __FUNCTION__);

      // update checkout terms
      if ($terms) {
        $this->drService->updateCheckoutTerms($subscription->getDrCheckoutId(), $terms);
        $this->result->appendMessage('dr-checkout terms updated', location: __FUNCTION__);
      }

      // convert checkout to order
      $drOrder = $this->drService->convertCheckoutToOrder($subscription->getDrCheckoutId());
      $this->result->appendMessage("dr-checkout converted to dr-order for subscription ({$subscription->id})", location: __FUNCTION__);

      // update invoice
      $invoice = $subscription->getActiveInvoice();
      $invoice->fillFromDrObject($drOrder);
      $invoice->invoice_date = now();
      $invoice->setDrOrderId($drOrder->getId());
      $invoice->setStatus(Invoice::STATUS_PENDING);
      $invoice->save();
      $this->result->appendMessage("invoice ({$invoice->id}) updated from drObject => pending", location: __FUNCTION__);

      // update subscription
      $subscription->fillPaymentMethod($paymentMethod);
      $subscription->fillAmountFromDrObject($drOrder);
      $subscription->setDrOrderId($drOrder->getId());
      $subscription->setStatus(Subscription::STATUS_PENDING);
      $subscription->sub_status = ($drOrder->getState() == 'accepted') ? Subscription::SUB_STATUS_NORMAL : Subscription::SUB_STATUS_ORDER_PENDING;
      $subscription->save();
      $this->result->appendMessage("subscription ({$subscription->id}) updated from drObject => pending/{$subscription->sub_status}", location: __FUNCTION__);

      return $subscription;
    } catch (\Throwable $th) {
      throw $th;
    }
  }

  public function stopSubscription(Subscription $subscription, string $reason): Subscription
  {
    return $this->stopOrFailSubscription($subscription, Subscription::STATUS_STOPPED, $reason);
  }

  public function failSubscription(Subscription $subscription, string $reason): Subscription
  {
    return $this->stopOrFailSubscription($subscription, Subscription::STATUS_FAILED, $reason);
  }

  protected function stopOrFailSubscription(Subscription $subscription, string $status,  string $reason): Subscription
  {
    $currentStatus = $subscription->status;
    if ($currentStatus == Subscription::STATUS_STOPPED || $currentStatus == Subscription::STATUS_FAILED) {
      $this->result->appendMessage("subscription ({$subscription->id}) already stopped or failed", location: __FUNCTION__);
      return $subscription;
    }

    $subscription->stop($status, $reason);
    $this->result->appendMessage("subscription ({$subscription->id}) $status", location: __FUNCTION__);

    // pro subscription stopped
    if ($currentStatus == Subscription::STATUS_ACTIVE && $subscription->subscription_level > 1) {
      // cancel renewal if any
      $renewal = $subscription->cancelPendingOrActiveRenewal();
      if ($renewal) {
        $this->result->appendMessage("subscription ({$subscription->id}) renewal cancelled", location: __FUNCTION__);
      }

      // update license sharing
      $licenseSharing = $subscription->user->getActiveLicenseSharing();
      if ($licenseSharing) {
        // refreshLicenseSharing will update user's subscription level
        $this->licenseService->refreshLicenseSharing($licenseSharing);
        $this->result->appendMessage("license-sharing refreshed for user ({$licenseSharing->user_id}) ", location: __FUNCTION__);
      }
    }

    if ($currentStatus == Subscription::STATUS_ACTIVE && empty($licenseSharing)) {
      $subscription->user->updateSubscriptionLevel();
      $this->result->appendMessage("user ({$subscription->user_id}) subscription level updated", location: __FUNCTION__);
    }

    return $subscription;
  }

  public function cancelSubscription(Subscription $subscription, bool $needRefund = false, bool $immediate = false): Subscription
  {
    try {
      $invoiceToRefund = null;
      if ($needRefund) {
        $result = RefundRules::customerRefundable($subscription);
        if (!$result['refundable']) {
          throw new Exception("try to refund non-refundable subscription ({$subscription->id})", 500);
        }

        /** @var Invoice $invoiceToRefund */
        $invoiceToRefund = $result['invoice'];
        if ($invoiceToRefund->status == Invoice::STATUS_PROCESSING) {
          // refund later
          $invoiceToRefund->setSubStatus(Invoice::SUB_STATUS_TO_REFUND);
          $invoiceToRefund->save();
          $this->result->appendMessage("invoice ({$invoiceToRefund->id}) updated => to-refund", location: __FUNCTION__);
        } else {
          // refund immediately
          $refund = $this->createRefund($invoiceToRefund, reason: 'cancel subscription with refund option');
          $this->result->appendMessage("refund ({$refund->id}) created", location: __FUNCTION__);
        }
      }

      $drSubscription = $this->drService->cancelSubscription($subscription->dr_subscription_id);
      $this->result->appendMessage("dr-subscription for subscription ({$subscription->id}) cancelled", location: __FUNCTION__);

      $activeInvoice = $subscription->getActiveInvoice();
      if ($activeInvoice && $activeInvoice->id != $invoiceToRefund?->id) {
        $activeInvoice->setStatus(Invoice::STATUS_CANCELLED);
        $activeInvoice->save();
        $this->result->appendMessage("active invoice ({$activeInvoice->id}) updated => cancelled", location: __FUNCTION__);
      }

      if ($invoiceToRefund || $subscription->isFreeTrial()) {
        $this->stopSubscription($subscription, $invoiceToRefund ? 'cancelled with refund' : 'cancell during free trial');
        $this->result->appendMessage("subscription ({$subscription->id}) stopped: refunded or is free-trial", location: __FUNCTION__);
      } else {
        $subscription->end_date =
          $drSubscription->getCurrentPeriodEndDate() ? Carbon::parse($drSubscription->getCurrentPeriodEndDate()) : null;
        $subscription->sub_status = Subscription::SUB_STATUS_CANCELLING;
        $subscription->next_invoice_date = null;
        $subscription->next_invoice = null;
        $subscription->active_invoice_id = null;
        $subscription->save();
        $this->result->appendMessage("subscription ({$subscription->id}) updated => cancelling", location: __FUNCTION__);

        $renewal = $subscription->cancelPendingOrActiveRenewal();
        if ($renewal) {
          $this->result->appendMessage("subscription ({$subscription->id}) renewal cancelled", location: __FUNCTION__);
        };
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
      throw new Exception("subscription ({$subscription->id}) has no active renewal", 500);
    }

    // complete renewal
    $subscription->completeActiveRenewal();
    $this->result->appendMessage("subscription ({$subscription->id}) active renewal completed (if exists)", location: __FUNCTION__);

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
    if (!$invoice->isCancellable()) {
      throw new Exception("invoice ({$invoice->id}) is not cancellable", 500);
    }

    $subscription = $invoice->subscription;
    try {
      $this->drService->fulfillOrder($invoice->getDrOrderId(), null, true);
      $this->result->appendMessage("dr-order for invoice ({$invoice->id}) cancelled", location: __FUNCTION__);

      $invoice->setStatus(Invoice::STATUS_CANCELLED);
      $invoice->save();
      $this->result->appendMessage("invoice ({$invoice->id}) updated => cancelled", location: __FUNCTION__);

      $this->failSubscription($subscription, 'manually cancelled');
      $this->result->appendMessage("subscription ({$subscription->id}) failed: manually cancelled", location: __FUNCTION__);

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
      throw new Exception("invoice ({$invoice->id}) is not refundable", 500);
    }
    // adjust amount
    if ($amount == 0) {
      $amount = $invoice->total_amount - $invoice->total_refunded;
    }

    $refund = Refund::newFromInvoice($invoice, $amount, $reason);
    $this->result->appendMessage("refund ({$refund->id}) created", location: __FUNCTION__);

    $drRefund = $this->drService->createRefund($refund);
    $this->result->appendMessage('dr-refund created', location: __FUNCTION__);

    $refund->setDrRefundId($drRefund->getId());
    $refund->save();
    $this->result->appendMessage("refund ({$refund->id}) dr-refund-id updated", location: __FUNCTION__);

    $invoice->setStatus(Invoice::STATUS_REFUNDING);
    $invoice->save();
    $this->result->appendMessage("invoice ({$invoice->id}) status updated => refunding", location: __FUNCTION__);

    return $refund;

    // note: notification will be sent from event handler
  }

  public function createRefundFromDrObject(DrOrderRefund $drRefund): Refund|null
  {
    // if created from createRefund() or already created, skip
    if ($drRefund->getMetadata()) {
      $this->result->appendMessage("dr-refund ({$drRefund->getId()}) is already in creating ...", location: __FUNCTION__);
      return null;
    }

    if ($refund = Refund::findByDrRefundId($drRefund->getId())) {
      $this->result->appendMessage("refund ({$refund->id}) already created", location: __FUNCTION__);
      return $refund;
    }

    $invoice = Invoice::findByDrOrderId($drRefund->getOrderId());
    if (!$invoice) {
      $this->result->appendMessage("no invoice found for dr-refund ({$drRefund->getId()})", location: __FUNCTION__);
      return null;
    }

    $refund = Refund::newFromInvoice($invoice, $drRefund->getAmount(), $drRefund->getReason());
    $refund->setDrRefundId($drRefund->getId());
    $refund->save();
    $this->result->appendMessage("refund ({$refund->id}) created from dr-refund", location: __FUNCTION__);

    $invoice->setStatus(Invoice::STATUS_REFUNDING);
    $invoice->save();
    $this->result->appendMessage("invoice ({$invoice->id}) status updated => refunding", location: __FUNCTION__);

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
      $this->result->appendMessage("dr-customer created for user ({$user->id})", location: __FUNCTION__);

      $user->setDrCustomerId($customer->getId());
      $user->save();
      $this->result->appendMessage("user ({$user->id}) updated: dr.customer_id", location: __FUNCTION__);
    } else {
      $customer = $this->drService->updateCustomer($user->getDrCustomerId(), $billingInfo);
      $this->result->appendMessage("dr-customer updated for user ({$user->id})", location: __FUNCTION__);
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
    $this->result->appendMessage("dr-source attached for user ({$user->id})", location: __FUNCTION__);

    // detach previous source
    if ($previousSourceId) {
      $this->drService->detachCustomerSource($user->getDrCustomerId(), $previousSourceId);
      $this->result->appendMessage("old dr-source detached for user ({$user->id})", location: __FUNCTION__);

      // TODO: send notifcation to notify user payment method updated
    }

    // attach source to active subscription
    $subscription = $user->getActiveLiveSubscription();
    if ($subscription) {
      $this->drService->updateSubscriptionSource($subscription->dr_subscription_id, $source->getId());
      $this->result->appendMessage("dr-source attached to dr-subscription for subscription ({$subscription->id})", location: __FUNCTION__);
    }

    $paymentMethod = $paymentMethod ?: new PaymentMethod(['user_id' => $user->id]);

    $paymentMethod->fillFromDrObject($source);
    $paymentMethod->save();
    $this->result->appendMessage("payment-method created/updated from drObject for user ({$user->id})", location: __FUNCTION__);

    // update active subscription
    if ($subscription) {
      $subscription->fillPaymentMethod($paymentMethod);
      $subscription->save();
      $this->result->appendMessage("subscription ({$subscription->id}) updated: payment_method_info", location: __FUNCTION__);
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
    $this->result->appendMessage("dr-taxId created for user ({$user->id})", location: __FUNCTION__);

    if ($drTaxId->getState() == DrCustomerTaxIdentifier::STATE_NOT_VALID || !isset($drTaxId->getApplicability()[0])) {
      $this->drService->deleteTaxId($drTaxId->getId());
      throw new \Exception('tax id is invalid', 400);
    }

    if ($taxId) {
      // delete old taxId
      $this->drService->deleteTaxId($taxId->dr_tax_id);
      $this->result->appendMessage("old dr-taxId removed for user ({$user->id})", location: __FUNCTION__);
    }

    $this->drService->attachCustomerTaxId($user->getDrCustomerId(), $drTaxId->getId());
    $this->result->appendMessage("dr-taxId attached to dr-customer for user ({$user->id})", location: __FUNCTION__);

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
    $this->result->appendMessage("taxId ({$taxId->id}) created", location: __FUNCTION__);

    return $taxId;
  }

  public function deleteTaxId(TaxId $taxId)
  {
    // TODO: need check whether tax id is in use?

    // delete old taxId
    $this->drService->deleteTaxId($taxId->dr_tax_id);
    $this->result->appendMessage("dr-taxId removed for user ({$taxId->user_id})", location: __FUNCTION__);

    $result = $taxId->delete();
    $this->result->appendMessage("taxId ({$taxId->id}) deleted", location: __FUNCTION__);

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
    DrEventRawRecord::createIfNotExist($event['id'], $event);

    // init context
    $this->result
      ->init(SubscriptionManagerResult::CONTEXT_WEBHOOK)
      ->setEventType($event['type'])
      ->setEventId($event['id']);

    // find event handler
    $eventHandler = $this->eventHandlers[$this->result->getEventType()] ?? null;
    if (!$eventHandler || !method_exists($this, $eventHandler['handler'])) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_IGNORED)
        ->appendMessage("event {$this->result->getEventType()} ignored: no-handler", location: __FUNCTION__);
      return response()->json($this->result->getData());
    }

    // find record
    $drEvent = DrEventRecord::fromDrEventIdOrNew($this->result->getEventId(), $this->result->getEventType());
    if ($drEvent->isCompleted()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_IGNORED)
        ->appendMessage("event {$this->result->getEventType()} ignored: duplicated", location: __FUNCTION__,);
      return response()->json($this->result->getData());
    }
    if ($drEvent->isProcessing()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_FAILED)
        ->appendMessage("event {$this->result->getEventType()} failed: already in processing", location: __FUNCTION__);
      return response()->json($this->result->getData(), 409); // return 409 to ask DR to retry
    }

    try {
      $drEvent->startProcessing();
      $this->result->appendMessage("event {$this->result->getEventType()} processing", location: __FUNCTION__);

      $object = DrObjectSerializer::deserialize($event['data']['object'], $eventHandler['class']);
      $handler = $eventHandler['handler'];
      $this->$handler($object);

      $this->result->appendMessage("event {$this->result->getEventType()} processed", location: __FUNCTION__);
      $drEvent->complete($this->result);

      return response()->json($this->result->getData());
    } catch (\Throwable $th) {
      if ($th instanceof WebhookException) {
        $this->result
          ->setResult(SubscriptionManagerResult::RESULT_FAILED)
          ->appendMessage("event {$this->result->getEventType()} WebhookExcetpion: {$th->getMessage()}", location: __FUNCTION__, level: 'warning');
        $drEvent->fail($this->result);
      } else {
        $this->result
          ->setResult(SubscriptionManagerResult::RESULT_EXCEPTION)
          ->appendMessage("event {$this->result->getEventType()} OtherException: {$th->getMessage()}", location: __FUNCTION__, level: 'error');
        $drEvent->fail($this->result);
      }
      return response()->json($this->result->getData(), 400);
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
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("dr-order ({$order->getId()}) skipped: no valid dr-subscription", location: $__FUNCTION__, level: 'warning');
      return null;
    }

    /** @var Subscription|null $subscription */
    $subscription = Subscription::where('dr_subscription_id', $drSubscriptionId)->first();
    if (!$subscription) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("dr-order ({$order->getId()}) skipped: no invalid subscription", location: $__FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setSubscription($subscription);

    // only process the first order
    if ($options['be_first'] ?? false) {
      if ($subscription->getDrCheckoutId() !== $order->getCheckoutId()) {
        $this->result
          ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
          ->appendMessage("dr-order ({$order->getId()}) skipped: not the first one", location: $__FUNCTION__);
        return null;
      }

      $this->wait(
        check: fn() => $subscription->getDrOrderId() == $order->getId(),
        postCheck: fn() => $subscription->refresh(),
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

    // must be in pending status
    if ($subscription->status != Subscription::STATUS_PENDING) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription skipped: not in pending', location: __FUNCTION__);
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    $this->result->setInvoice($invoice);

    // fulfill order
    try {
      $this->drService->fulfillOrder($drOrder->getId(), $drOrder);
      $this->result->appendMessage('dr-order fulfilled', location: __FUNCTION__);
    } catch (\Throwable $th) {
      $invoice->setStatus(Invoice::STATUS_FAILED);
      $invoice->save();
      $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

      $this->failSubscription($subscription, 'fulfill dr-order fails');
      $this->result->appendMessage('subscription failed: fulfillment failed', location: __FUNCTION__, level: 'warning');

      $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);

      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
        ->appendMessage('fulfill order fails', location: __FUNCTION__);
      return null;
    }

    // activate dr subscription
    $drSubscription = $this->drService->activateSubscription($subscription->dr_subscription_id);
    $this->result->appendMessage('dr-subscription activated', location: __FUNCTION__);

    // cancel previous subscription
    if ($previousSubscription = $subscription->user->getActiveLiveSubscription()) {
      $this->cancelSubscription($previousSubscription);
      $this->result->appendMessage('previous subscription cancelled', location: __FUNCTION__);
    }

    // stop previous subscription
    if ($previousSubscription = $subscription->user->getActiveSubscription()) {
      // stop subscription and do not update user subscription level to avoid create new basic subscription
      $previousSubscription->stop(Subscription::STATUS_STOPPED, 'new subscription activated');
      $this->result->appendMessage('previous subscription stopped', location: __FUNCTION__);
    }

    // active current subscription
    $subscription->fillAmountFromDrObject($drOrder);
    $subscription->fillPeriodFromDrObject($drSubscription);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->sub_status = Subscription::SUB_STATUS_NORMAL;
    $subscription->fillNextInvoice();
    $subscription->active_invoice_id = null;
    $subscription->save();
    $this->result->appendMessage('subscription updated from drObject => active', location: __FUNCTION__);

    // update dr subscription
    if ($subscription->isNextPlanDifferent()) {
      $this->drService->convertSubscriptionToNext($drSubscription, $subscription);
      $this->result->appendMessage('dr-subscription converted to next', location: __FUNCTION__);
    }

    // active license sharing if there is license package
    if ($subscription->license_package_info && $subscription->license_package_info['quantity'] > 0) {
      $this->licenseService->createLicenseSharing($subscription);
      $this->result->appendMessage('license sharing created', location: __FUNCTION__);
    } else {
      // update user subscription level
      $subscription->user->updateSubscriptionLevel();
      $this->result->appendMessage('user subscription level updated', location: __FUNCTION__);
    }

    // update invoice status
    $invoice->fillPeriod($subscription);
    $invoice->fillFromDrObject($drOrder);
    $invoice->setStatus(Invoice::STATUS_PROCESSING);
    $invoice->save();
    $this->result->appendMessage('invoice updated from drObject => processing', location: __FUNCTION__);

    if ($subscription->coupon_info) {
      $subscription->coupon->setUsage($subscription->id)->save();
      $this->result->appendMessage('coupon usage updated', location: __FUNCTION__);
    }

    // create renewal if required
    $renewal = $subscription->createRenewal();
    if ($renewal) {
      $this->result->appendMessage('subscription renewal created', location: __FUNCTION__);
    }

    // log event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_ACTIVATED, $subscription);

    // disptch event
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED, $invoice);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_CONFIRMED, $invoice);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onOrderBlocked(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription already in failed', location: __FUNCTION__);
      return null;
    }

    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    $this->result->setInvoice($invoice);
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

    // update subscription status
    $this->failSubscription($subscription, 'first order being blocked');
    $this->result->appendMessage('subscription failed: first order being blocked', location: __FUNCTION__);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onOrderCancelled(DrOrder $drOrder): Subscription|null
  {
    // validate the order
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription already in failed', location: __FUNCTION__);
      return null;
    }

    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    $this->result->setInvoice($invoice);
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

    $this->failSubscription($subscription, 'first order being cancelled');
    $this->result->appendMessage('subscription failed: first order being cancelled', location: __FUNCTION__);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onOrderChargeFailed(DrCharge $charge): Subscription|null
  {
    // skip charge that is not related to order (for renew subscription)
    if (!$charge->getOrderId()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('charge skipped: no valid dr-order', ['charge_id' => $charge->getId()], location: __FUNCTION__, level: 'warning');
      return null;
    }

    // validate the order
    $drOrder = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription already in failed', location: __FUNCTION__);
      return null;
    }

    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    $this->result->setInvoice($invoice);
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

    $this->failSubscription($subscription, 'first order charge failed');
    $this->result->appendMessage('subscription failed: first order charge failed', location: __FUNCTION__);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onOrderChargeCaptureComplete(DrCharge $charge): Invoice|null
  {
    // validate the charger
    if (!$charge->getOrderId()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('charge skipped: no valid dr-order-id', location: __FUNCTION__, level: 'warning');
      return null;
    }

    $drOrder = $this->drService->getOrder($charge->getOrderId());
    $subscription = $this->validateOrder($drOrder, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    $this->result->setInvoice($invoice);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
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

    // skip failed
    if ($subscription->status == Subscription::STATUS_FAILED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription already in failed', location: __FUNCTION__, level: 'warning');
      return null;
    }

    if ($subscription->status == Subscription::STATUS_ACTIVE) {
      $this->drService->cancelSubscription($subscription->dr_subscription_id);
      $this->result->appendMessage('dr-subscription cancelled', location: __FUNCTION__);
    }

    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    $this->result->setInvoice($invoice);
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated: failed', location: __FUNCTION__);

    $this->failSubscription($subscription, 'first order charge capture failed');
    $this->result->appendMessage('subscription failed: first order charge capture failed', location: __FUNCTION__);

    if ($subscription->coupon_info) {
      $subscription->coupon->releaseUsage($subscription->id);
      $this->result->appendMessage("coupon usage updated: status = {$subscription->coupon->status}", location: __FUNCTION__);
    }

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_ABORTED, $invoice);

    // this is an unsure case, send bugsnag to notify
    Bugsnag::notifyError('onOrderChargeCaptureFailed()', "You need double check invoice: {$invoice->id} status");

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderComplete(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      return null;
    }
    $this->result->setInvoice($invoice);

    // DR-BUG: $drOrder->getState() is not completed
    if ($drOrder->getState() !== DrOrder::STATE_COMPLETE) {
      $drOrder = $this->drService->getOrder($drOrder->getId());
      if ($drOrder->getState() !== DrOrder::STATE_COMPLETE) {
        throw new WebhookException(__FUNCTION__ . ":the state of dr-order in order.complete event is {$drOrder->getState()}.");
      }
    }

    // wait for onOrderAccepted to complete
    $this->wait(
      check: fn() => $invoice->period > 0,
      postCheck: fn() => $invoice->refresh(),
      location: __FUNCTION__,
      message: 'onOrderAccepted() to complete'
    );

    if ($invoice->status == Invoice::STATUS_COMPLETED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('invoice skipped: already in completed', location: __FUNCTION__);
      return null;
    }

    if ($invoice->sub_status == Invoice::SUB_STATUS_TO_REFUND) {
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      $this->result->appendMessage('invoice updated: completed', location: __FUNCTION__);

      $this->createRefund($invoice, reason: 'refund when order completed');
      $invoice->save();
      $this->result->appendMessage('refund created', location: __FUNCTION__);
    } else {
      $invoice->setStatus(Invoice::STATUS_COMPLETED);
      $invoice->save();
      $this->result->appendMessage('invoice updated: completed', location: __FUNCTION__);
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderInvoiceCreated(array $orderInvoice): Invoice|null
  {
    // validate order
    $invoice = Invoice::findByDrOrderId($orderInvoice['orderId']);
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("invoice not found from drOrderId {$orderInvoice['orderId']}", location: __FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setInvoice($invoice);

    // skip duplicated invoice
    if ($invoice->pdf_file) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('invoice skipped: pdf file already exists', location: __FUNCTION__, level: 'warning');
      return $invoice;
    }

    // create invoice pdf download link
    $fileLink = $this->drService->createFileLink($orderInvoice['fileId'], now()->addYears(10));
    $this->result->appendMessage('pdf file link created', location: __FUNCTION__);

    // update invoice
    $invoice->pdf_file = $fileLink->getUrl();
    $invoice->setDrFileId($orderInvoice['fileId']);
    $invoice->save();
    $this->result->appendMessage('invoice updated drFileId', location: __FUNCTION__);

    // sent notification
    $invoice->subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_INVOICE, $invoice);
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderCreditMemoCreated(array $orderCreditMemo): Invoice|null
  {
    // validate order
    $invoice = Invoice::findByDrOrderId($orderCreditMemo['orderId']);
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('invoice skipped: no valid credit memo', ['dr_order_id' => $orderCreditMemo['orderId']], location: __FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setInvoice($invoice);

    // skip duplicated invoice
    if ($invoice->findCreditMemoByFileId($orderCreditMemo['fileId']) !== false) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('invoice skipped: credit memo already exists', location: __FUNCTION__, level: 'warning');
      return null;
    }

    // create credit memo download link
    $fileLink = $this->drService->createFileLink($orderCreditMemo['fileId'], now()->addYear());
    $this->result->appendMessage('credit memo link created', location: __FUNCTION__);

    // update invoice
    $invoice->addCreditMemo($orderCreditMemo['fileId'], $fileLink->getUrl());
    $invoice->save();
    $this->result->appendMessage('invoice updated: credit memo added', location: __FUNCTION__);

    // sent notification
    $invoice->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
      $invoice,
      ['credit_memo' => $fileLink->getUrl()]
    );
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderDispute(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('no valid invoice', ['dr_order_id' => $drOrder->getId()], location: __FUNCTION__, level: 'warning');
      return null;
    }

    if (
      $invoice->getDisputeStatus() != Invoice::DISPUTE_STATUS_DISPUTING &&
      $invoice->getDisputeStatus() != Invoice::DISPUTE_STATUS_DISPUTED
    ) {
      $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_DISPUTING);
      $invoice->save();
      $this->result->appendMessage('invoice disputing', location: __FUNCTION__);
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderDisputeResolved(DrOrder $drOrder): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($drOrder->getId());
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('no valid invoice', ['dr_order_id' => $drOrder->getId()], location: __FUNCTION__, level: 'warning');
      return null;
    }

    if ($invoice->getDisputeStatus() == Invoice::DISPUTE_STATUS_DISPUTING) {
      $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_NONE);
      $invoice->save();
      $this->result->appendMessage('invoice dispute resolved', location: __FUNCTION__);
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderChargeback(DrSalesTransaction $salesTransaction): Invoice|null
  {
    if ($salesTransaction->getAmount() == 0) {
      // skip chargeback fee event
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('chargeback fee event skipped', location: __FUNCTION__);
      return null;
    }

    $invoice = Invoice::findByDrOrderId($salesTransaction->getOrderId());
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('no valid invoice', ['dr_order_id' => $salesTransaction->getOrderId()], location: __FUNCTION__, level: 'error');
      return null;
    }
    $this->result->setInvoice($invoice);

    $subscription = $invoice->subscription;
    if (
      $subscription->status == Subscription::STATUS_ACTIVE &&
      $subscription->sub_status != Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->cancelSubscription($subscription);
      $this->result->appendMessage('subscription cancelled', location: __FUNCTION__, level: 'warning');
    }

    $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_DISPUTED);
    $invoice->save();
    $this->result->appendMessage('invoice disputed', location: __FUNCTION__);

    $user = $subscription->user;
    $user->type = User::TYPE_BLACKLISTED;
    $user->save();
    $this->result->appendMessage('user blacklisted', location: __FUNCTION__);

    // invoice is chargebacked
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_REFUNDED, $invoice, null);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $invoice;
  }

  protected function onOrderRefunded(DrOrder $order): Invoice|null
  {
    $invoice = Invoice::findByDrOrderId($order->getId());
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('no valid invoice', ['dr_order_id' => $order->getId()], location: __FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setInvoice($invoice);

    if ($invoice->status != Invoice::STATUS_REFUNDED) {
      $invoice->total_refunded = $order->getRefundedAmount();
      $invoice->setStatus($order->getAvailableToRefundAmount() > 0 ?
        Invoice::STATUS_PARTLY_REFUNDED :
        Invoice::STATUS_REFUNDED);
      $invoice->save();
      $this->result->appendMessage('invoice updated: refunded amount and status', location: __FUNCTION__);

      $invoice->subscription->sendNotification(SubscriptionNotification::NOTIF_ORDER_REFUNDED, $invoice);
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
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
    $this->result->appendMessage('first invoice created', location: __FUNCTION__);

    $subscription->active_invoice_id = $invoice->id;
    $subscription->save();
    $this->result->appendMessage('subscription updated: active_invoice_id', location: __FUNCTION__);

    return $invoice;
  }

  protected function createRenewInvoice(Subscription $subscription, DrInvoice $drInvoice): Invoice
  {
    if ($subscription->invoices()->where('period', $subscription->current_period + 1)->count()) {
      throw new Exception('try to create duplicated renew invoice for same period', 500);
    }

    $invoice = Invoice::findByDrInvoiceId($drInvoice->getId());
    if ($invoice) {
      throw new Exception('invoice with same dr_invoice_id exists.', 500);
    }

    $invoice = new Invoice();
    $invoice->fillBasic($subscription, true)
      ->fillPeriod($subscription, true)
      ->fillFromDrObject($drInvoice)
      ->setDrInvoiceId($drInvoice->getId())
      ->setDrOrderId($drInvoice->getOrderId())
      ->setStatus(Invoice::STATUS_INIT)
      ->save();
    $this->result->appendMessage('renew invoice created', location: __FUNCTION__);

    $subscription->active_invoice_id = $invoice->id;
    $subscription->save();
    $this->result->appendMessage('subscription updated: active_invoice_id', location: __FUNCTION__);

    return $invoice;
  }

  protected function createFailedRenewInvoice(Subscription $subscription): Invoice
  {
    if ($subscription->active_invoice_id) {
      throw new Exception('Try to create duplicated invoice', 500);
    }

    $invoice = new Invoice();
    $invoice->fillBasic($subscription, true)
      ->fillPeriod($subscription, true)
      ->fillFromSubscriptionNext($subscription)
      ->setDrOrderId(null)
      ->setStatus(Invoice::STATUS_FAILED)
      ->save();
    $this->result->appendMessage('failed invoice created', location: __FUNCTION__);

    $subscription->active_invoice_id = $invoice->id;
    $subscription->save();
    $this->result->appendMessage('subscription updated: active_invoice_id', location: __FUNCTION__);

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
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription skipped: no valid subscription', ['dr_subscription_id' => $drSubscription->getId()], location: $__FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setSubscription($subscription);

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
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription not in active', location: __FUNCTION__, level: 'warning');
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if (!$invoice) {
      $invoice = $this->createRenewInvoice($subscription, $drInvoice);
      $this->result->appendMessage('renew invoice created', location: __FUNCTION__);
    }
    $this->result->setInvoice($invoice);

    // update DrOrder
    try {
      // TODO: temp fix for invoice not in paid state
      if ($drInvoice->getState() !== DrInvoice::STATE_PAID || !$drInvoice->getOrderId()) {
        $drInvoice = $this->drService->getInvoice($drInvoice->getId());
      }

      $this->drService->updateOrderUpstreamId($drInvoice->getOrderId(), $invoice->id);
      $this->result->appendMessage('dr-order updated: upstream id', location: __FUNCTION__);
    } catch (\Throwable $th) {
      $this->result->appendMessage('update dr order upstream id failed', location: __FUNCTION__, level: 'error');
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
    $this->result->appendMessage('subscription updated', location: __FUNCTION__);

    // update dr subscription
    if ($subscription->isNextPlanDifferent()) {
      $this->drService->convertSubscriptionToNext($drSubscription, $subscription);
      $this->result->appendMessage('dr-subscription converted to next', location: __FUNCTION__);
    }

    // create renewal is required
    $renewal = $subscription->createRenewal();
    if ($renewal) {
      $this->result->appendMessage('subscription renewal created', location: __FUNCTION__);
    }

    // update invoice
    $invoice->fillFromDrObject($drInvoice);
    $invoice->setDrOrderId($drInvoice->getOrderId());
    $invoice->setStatus(Invoice::STATUS_PROCESSING);
    $invoice->save();
    $this->result->appendMessage('invoice updated from drObject', location: __FUNCTION__);

    // log subscription event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_EXTENDED, $subscription);

    // disptch event
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED, $invoice);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_EXTENDED, $invoice);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onSubscriptionFailed(DrSubscription $drSubscription): Subscription|null
  {
    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription skipped: not in active', ['subscription_id' => $drSubscription->getId()], location: __FUNCTION__);
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if (!$invoice) {
      $invoice = $this->createFailedRenewInvoice($subscription);
      $this->result->appendMessage('failed invoice created', location: __FUNCTION__);
    }
    $this->result->setInvoice($invoice);

    // stop subscription data
    $this->failSubscription($subscription, 'renew failed');
    $this->result->appendMessage('subscription stoped: renewal failed', location: __FUNCTION__);

    // stop invoice
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

    // log subscription event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_FAILED, $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_FAILED, $invoice);
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onSubscriptionSourceInvalid(DrSubscription $drSubscription): Subscription|null
  {
    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if (
      $subscription->status != Subscription::STATUS_ACTIVE
      || $subscription->sub_status == Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription not in active or in cancelling status', location: __FUNCTION__, level: 'warning');
      return null;
    }

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_SOURCE_INVALID);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onSubscriptionLapsed(DrSubscription $drSubscription): Subscription|null
  {
    $subscription = $this->validateSubscription($drSubscription, __FUNCTION__: __FUNCTION__);
    if (!$subscription) {
      return null;
    }

    if ($subscription->status != Subscription::STATUS_ACTIVE) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription not in active', location: __FUNCTION__, level: 'warning');
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if (!$invoice) {
      $invoice = $this->createFailedRenewInvoice($subscription);
      $this->result->appendMessage('lapsed invoice created', location: __FUNCTION__);
    }
    $this->result->setInvoice($invoice);

    // stop subscription data
    $this->failSubscription($subscription, 'subscription lapsed');

    // stop invoice
    $invoice->setStatus(Invoice::STATUS_FAILED);
    $invoice->save();
    $this->result->appendMessage('invoice updated => failed', location: __FUNCTION__);

    // log subscription event
    SubscriptionLog::logEvent(SubscriptionLog::SUBSCRIPTION_FAILED, $subscription);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_LAPSED, $invoice);
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
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
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription not in active or in cancelling status', location: __FUNCTION__, level: 'warning');
      return null;
    }

    $invoice = $subscription->getActiveInvoice();
    if (!$invoice) {
      $invoice = $this->createRenewInvoice($subscription, $drInvoice);
      $this->result->appendMessage('renew invoice created', location: __FUNCTION__);
    }
    $this->result->setInvoice($invoice);

    $invoice->fillFromDrObject($drInvoice);
    $invoice->setStatus(Invoice::STATUS_PENDING);
    $invoice->save();
    $this->result->appendMessage('invoice updated => pending', location: __FUNCTION__);

    // update subscription
    $subscription->fillNextInvoiceAmountFromDrObject($drInvoice);
    $subscription->save();
    $this->result->appendMessage('subscription amount updated from dr object ', location: __FUNCTION__);

    // send notification
    $subscription->sendNotification(SubscriptionNotification::NOTIF_INVOICE_PENDING, $invoice);
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
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
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('subscription not in active or in cancelling status', location: __FUNCTION__, level: 'warning');
      return null;
    }

    // create invoice
    $invoice = $this->createRenewInvoice($subscription, $drInvoice);
    $this->result
      ->setInvoice($invoice)
      ->appendMessage('renew invoice created', location: __FUNCTION__);

    // update subscription
    $subscription->fillNextInvoiceAmountFromDrObject($drInvoice);
    $subscription->save();
    $this->result->appendMessage('subscription amount updated from dr object ', location: __FUNCTION__);

    // send notification
    if ($subscription->isRenewalPendingOrActive()) {
      $subscription->sendRenewNotification();
    } else {
      $subscription->sendNotification(SubscriptionNotification::NOTIF_REMINDER);
    }
    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $subscription;
  }

  protected function onTaxIdStateChange(DrTaxId $drTaxId): TaxId|null
  {
    //
    $id = $drTaxId->getId();

    /** @var TaxId|null @taxId */
    $taxId = TaxId::where('dr_tax_id', $id)->first();
    if (!$taxId) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('tax id not found', ['dr_tax_id' => $id], location: __FUNCTION__, level: 'warning');
      return null;
    }
    $this->result->setTaxId($taxId);

    if ($taxId->status != $drTaxId->getState()) {
      $taxId->status = $drTaxId->getState();
      $taxId->save();
      $this->result->appendMessage('tax id status updated', ['tax_id' => $taxId->id, 'status' => $taxId->status], location: __FUNCTION__);
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $taxId;
  }

  protected function onRefundPending(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if ($refund) {
      $this->result
        ->setRefund($refund)
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('refund already exist', location: __FUNCTION__);
      return null;
    }

    // create refund is not exist
    $refund = $this->createRefundFromDrObject($drRefund);
    if ($refund) {
      $this->result
        ->setRefund($refund)
        ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
        ->appendMessage('refund created/retrieved successfully', location: __FUNCTION__);
    } else {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('dr-refund can not be created', location: __FUNCTION__);
    }

    return $refund;
  }

  protected function onRefundFailed(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if (!$refund) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('refund not found', location: __FUNCTION__);
      return null;
    }
    $this->result->setRefund($refund);

    if ($refund->status != Refund::STATUS_FAILED) {
      $refund->status = Refund::STATUS_FAILED;
      $refund->save();
      $this->result->appendMessage('refund status updated => failed', location: __FUNCTION__);

      $invoice = $refund->invoice;
      $invoice->setStatus(Invoice::STATUS_REFUND_FAILED);
      $invoice->save();
      $this->result->appendMessage('invoice status updated => refund-failed', location: __FUNCTION__);
    }

    $refund->invoice->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,
      $refund->invoice,
      ['refund' => $refund]
    );

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $refund;
  }

  protected function onRefundComplete(DrOrderRefund $drRefund): Refund|null
  {
    $refund = Refund::findByDrRefundId($drRefund->getId());
    if (!$refund) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('refund not found', location: __FUNCTION__);
      return null;
    }
    $this->result->setRefund($refund);

    if ($refund->status != Refund::STATUS_COMPLETED) {
      $refund->status = Refund::STATUS_COMPLETED;
      $refund->save();
      $this->result->appendMessage('refund status updated => complete', location: __FUNCTION__);

      // send refunded event
      SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_REFUNDED, $refund->invoice, $refund);

      // invoice is not updated here, but in onOrderRefunded()
    }

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
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
      $this->result->appendMessage("try to complete invoice ({$invoice->id})", location: __FUNCTION__);
      $this->onOrderComplete($drOrder);
      $invoice->refresh();
      if ($invoice->getStatus() == Invoice::STATUS_COMPLETED) {
        $this->result->appendMessage("invoice ({$invoice->id}) completed", location: __FUNCTION__);
      }

      return true;
    } catch (\Throwable $th) {
      // silient
    }

    return false;
  }
}
