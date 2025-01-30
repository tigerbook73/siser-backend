<?php

namespace App\Services\DigitalRiver;

use App\Events\SubscriptionOrderEvent;
use App\Models\DrEventRawRecord;
use App\Models\DrEventRecord;
use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use App\Services\LicenseSharing\LicenseSharingService;
use App\Services\RefundRules;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Exception;


class SubscriptionManagerDR implements SubscriptionManager
{
  public $eventHandlers = [];

  public function __construct(public DigitalRiverService $drService, public LicenseSharingService $licenseService, public SubscriptionManagerResult $result)
  {
    $this->eventHandlers = [
      // order events

      // subscription events

      // subscription invoices

      // invoice events: see Invoice.md for state machine
      'order.credit_memo.created'     => ['class' => 'array',                     'handler' => 'onOrderCreditMemoCreated'],

      // refund events
      'refund.pending'                => ['class' => DrOrderRefund::class,        'handler' => 'onRefundPending'],
      'refund.failed'                 => ['class' => DrOrderRefund::class,        'handler' => 'onRefundFailed'],
      'refund.complete'               => ['class' => DrOrderRefund::class,        'handler' => 'onRefundComplete'],
    ];
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

  public function cancelSubscription(Subscription $subscription, bool $immediate): Subscription
  {
    // do nothing;
    return $subscription;
  }


  public function createRefund(Invoice $invoice, float $amount = 0, string $reason = null): Refund
  {
    $result = RefundRules::invoiceRefundable($invoice);
    if (!$result->isRefundable()) {
      throw new Exception("invoice ({$invoice->id}) is not refundable", 500);
    }
    // adjust amount
    if ($amount == 0) {
      $amount = $result->getRefundableAmount();
    }

    // create refund
    $refund = Refund::newFromInvoice($invoice, $amount, $reason);
    $this->result->appendMessage("refund ({$refund->id}) created", location: __FUNCTION__);

    $drRefund = $this->drService->createRefund($refund);
    $this->result->appendMessage('dr-refund created', location: __FUNCTION__);

    $refund->fillFromDrObject($drRefund);
    $refund->save();
    $this->result->appendMessage("refund ({$refund->id}) dr-refund-id updated", location: __FUNCTION__);

    $invoice->fillRefundStatus()->save();
    $this->result->appendMessage("invoice ({$invoice->id}) status updated => {$invoice->status}", location: __FUNCTION__);

    return $refund;
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

    // create refund
    $refund = Refund::newFromInvoice($invoice, $drRefund->getAmount(), $drRefund->getReason());
    $refund->fillFromDrObject($drRefund)->save();
    $this->result->appendMessage("refund ({$refund->id}) created from dr-refund", location: __FUNCTION__);

    $invoice->fillRefundStatus()->save();
    $this->result->appendMessage("invoice ({$invoice->id}) status updated", location: __FUNCTION__);

    return $refund;
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

    if ($refund->status === Refund::STATUS_FAILED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('refund already failed', location: __FUNCTION__);
      return $refund;
    }

    $refund->status = Refund::STATUS_FAILED;
    $refund->save();
    $this->result->appendMessage('refund status updated => failed', location: __FUNCTION__);

    $invoice = $refund->invoice;
    $drOrder = $this->drService->getOrder($drRefund->getOrderId());
    $invoice->fillFromDrObject($drOrder)
      ->fillRefundStatus()
      ->save();
    $this->result->appendMessage('invoice updated', location: __FUNCTION__);

    $invoice->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,
      $invoice,
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

    if ($refund->status === Refund::STATUS_COMPLETED) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('refund already completed', location: __FUNCTION__);
      return $refund;
    }

    $refund->status = Refund::STATUS_COMPLETED;
    $refund->save();
    $this->result->appendMessage('refund status updated => complete', location: __FUNCTION__);

    // update invoice
    $invoice = $refund->invoice;
    $drOrder = $this->drService->getOrder($drRefund->getOrderId());
    $invoice->fillFromDrObject($drOrder)
      ->fillRefundStatus()
      ->save();

    // send refunded event
    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_REFUNDED, $refund->invoice, $refund);

    // send notification
    $refund->subscription->sendNotification(
      SubscriptionNotification::NOTIF_ORDER_REFUNDED,
      $invoice
    );

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage('completed', location: __FUNCTION__);
    return $refund;
  }

  public function refreshInvoiceByDrOrder(Invoice $invoice)
  {
    if (!$invoice->getDrOrderId()) {
      return $invoice;
    }

    $drOrder = $this->drService->getOrder($invoice->getDrOrderId());
    $invoice->fillFromDrObject($drOrder)
      ->fillRefundStatus()
      ->save();
    $this->result->appendMessage("invoice ({$invoice->id}) updated from dr-order", location: __FUNCTION__);
    return $invoice;
  }
}
