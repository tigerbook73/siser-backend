<?php

namespace App\Services\Paddle;

use App\Models\Invoice;
use App\Models\PaddleMap;
use App\Models\Refund;
use App\Services\CurrencyHelper;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use Illuminate\Support\Carbon;
use Paddle\SDK\Entities\Adjustment as PaddleAdjustment;
use Paddle\SDK\Entities\Shared\Action;
use Paddle\SDK\Entities\Shared\AdjustmentStatus;
use Paddle\SDK\Entities\Shared\AdjustmentType;
use Paddle\SDK\Entities\Transaction as PaddleTransaction;
use Paddle\SDK\Notifications\Entities\Adjustment as NotificationPaddleAdjustment;
use Paddle\SDK\Notifications\Events\AdjustmentCreated;
use Paddle\SDK\Notifications\Events\AdjustmentUpdated;
use Paddle\SDK\Resources\Adjustments\Operations\CreateAdjustment;
use Paddle\SDK\Resources\Adjustments\Operations\Create\AdjustmentItem as CreateAdjustmentItem;


class AdjustmentService extends PaddleEntityService
{
  /**
   * do not set customerData
   *
   * Adjustment can be created from Refund of Dashboard
   *
   * onAdjustmentCreate/Updated():
   * 0. fetch transaction
   * 0. find adjustment from transaction.adjustments
   * 0. get invoice
   * 0. get refund
   *
   * if !invoice throw exception
   * if !refund create refund from adjustment
   * else if refund update refund from adjustment
   * if invoice update invoice from refund
   *
   * createRefundAdjustment($invoice, $amount, $reason)
   * 1. fetch transaction
   * 2. update invoice from transaction
   * 3. if invoice is not completed/partly-refunded, throw exception
   * 4. create adjustment
   * 5. create refund from adjustment
   *
   * refreshRefund($refund)
   * 1. fetch adjustment
   * 2. updateRefund($refund, $adjustment, $force = true)
   *
   * helper functions:
   * createRefund($invoice, $adjustment)
   * updateRefund($refund, $adjustment)
   * createAdjustmentFromTransaction($transaction, ?$amount, ?$items[item_id, amount], $reason)
   *
   * other consideration:
   * 1. timestamp: update refund only timestamp is newer or by force
   */

  /**
   * Create refund adjustment from invoice/transaction
   *
   * @param Invoice|PaddleTransaction $invoiceOrTransaction
   * @param float $amount
   * @param string $reason
   *
   * @return PaddleAdjustment
   */
  public function createAdjustment(Invoice|PaddleTransaction $invoiceOrTransaction, float $amount, string $reason): PaddleAdjustment
  {
    $invoice = $invoiceOrTransaction instanceof Invoice ? $invoiceOrTransaction : PaddleMap::findInvoiceByPaddleId($invoiceOrTransaction->id);
    $transaction = $invoiceOrTransaction instanceof Invoice ?
      $this->paddleService->getTransaction($invoiceOrTransaction->getMeta()->paddle->transaction_id) :
      $invoiceOrTransaction;
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_FAILED, 'invoice-not-found')
        ->appendMessage("invoice not found: {$invoiceOrTransaction->id}", location: __FUNCTION__);
      throw new \Exception('Invoice not found');
    }
    $this->result->setInvoice($invoice);

    // transform amount
    $lowestDenominationAmount = CurrencyHelper::getLowestDenominationPrice($transaction->currencyCode->getValue(), $amount);

    // type
    $type = ($lowestDenominationAmount == $transaction->details->totals->total) ?
      AdjustmentType::Full() :
      AdjustmentType::Partial();

    // build items
    $lineItems = collect($transaction->details->lineItems)->filter(fn($item) => $item->quantity > 0);
    $baseAmount = $lineItems->reduce(fn($carry, $item) => $carry + $item->totals->total, 0);
    $items = [];
    foreach ($lineItems as $transactionItem) {
      $items[] = new CreateAdjustmentItem(
        itemId: $transactionItem->id,
        type: $type,
        amount: $type == AdjustmentType::Full() ?
          null :
          (string)round($lowestDenominationAmount * ($transactionItem->totals->total / $baseAmount), 0)  // TODO: not correct if there are more than 2 item
      );
    }

    $createAdjustment = new CreateAdjustment(
      action: Action::Refund(),
      items: [],
      reason: $reason,
      transactionId: $transaction->id,
    );
    $adjustment = $this->paddleService->createAdjustment($createAdjustment);
    $this->result->appendMessage("adjustment {$adjustment->id} created from transaction {$transaction->id}", location: __FUNCTION__);

    // create refund from adjustment
    $refund = $this->createRefund($invoice, $adjustment);
    $this->result->setRefund($refund);

    return $adjustment;
  }

  /**
   * event handler for adjustment.created
   */
  public function onAdjustmentCreated(AdjustmentCreated $adjustmentCreated)
  {
    $this->onAdjustmentCreatedOrUpdated($adjustmentCreated->adjustment);
  }

  /**
   * event handler for adjustment.updated
   */
  public function onAdjustmentUpdated(AdjustmentUpdated $adjustmentUpdated)
  {
    $this->onAdjustmentCreatedOrUpdated($adjustmentUpdated->adjustment);
  }

  /**
   * internal function for adjustment.created/updated event
   */
  public function onAdjustmentCreatedOrUpdated(NotificationPaddleAdjustment $notificationAdjustment)
  {
    /**
     * 1. fetch transaction
     * 2. find adjustment from transaction.adjustments
     * 3. get invoice
     * 4. get refund
     *
     * if !invoice throw exception
     * if !refund create refund from adjustment
     * else if refund update refund from adjustment
     * if invoice update invoice from refund
     */

    // skip for non-refund adjustment (e.g. TODO: hargeback)
    if ($notificationAdjustment->action->getValue() != Action::Refund()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("skip non-refund adjustment {$notificationAdjustment->id}", location: __FUNCTION__);
      return;
    }

    $transaction = $this->paddleService->getTransaction($notificationAdjustment->transactionId);
    $adjustment = collect($transaction->adjustments)->first(fn($item) => $item->id == $notificationAdjustment->id);
    if (!$adjustment) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_FAILED, 'adjustment-not-found')
        ->appendMessage("adjustment not found: {$notificationAdjustment->id}", location: __FUNCTION__);
      throw new \Exception('Adjustment not found');
    }

    $invoice = PaddleMap::findInvoiceByPaddleId($adjustment->transactionId);
    if (!$invoice) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_FAILED, 'invoice-not-found')
        ->appendMessage("invoice not found: {$adjustment->transactionId}", location: __FUNCTION__);
      throw new \Exception('Invoice not found');
    }
    $this->result->setInvoice($invoice);

    $refund = PaddleMap::findRefundByPaddleId($adjustment->id);
    if (!$refund) {
      $refund = $this->createRefund($invoice, $adjustment);
    } else {
      $this->result->setRefund($refund);
      $this->updateRefund($refund, $adjustment);
    }

    // update invoice
    $this->manager->transactionService->createOrUpdateInvoice($refund->subscription, $transaction);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("adjustment create/updated event processed successfully", location: __FUNCTION__);
  }

  /**
   * Refresh refund from adjustment by force
   *
   * @param Refund $refund the refund to be refreshed
   */
  public function refreshRefund(Refund $refund): Refund
  {
    $adjustment = $this->paddleService->getAdjustment($refund->getMeta()->paddle->adjustment_id);
    return $this->updateRefund($refund, $adjustment, true);
  }

  /**
   * Create refund from adjustment
   *
   * @param Invoice $invoice the invoice to be refunded
   * @param PaddleAdjustment $adjustment the corresponding adjustment
   *
   * @return Refund
   */
  public function createRefund(Invoice $invoice, PaddleAdjustment $adjustment): Refund
  {
    $refund = new Refund([
      'user_id'              => $invoice->user_id,
      'subscription_id'      => $invoice->subscription_id,
      'invoice_id'           => $invoice->id,
      'currency'             => $invoice->currency,
      'item_type'            => Refund::ITEM_SUBSCRIPTION,
      'items'                => $invoice->items,
      'payment_method_info'  => $invoice->payment_method_info,
      'dr_refund_id'         => $adjustment->id,
    ]);
    $refund->setMetaPaddleTransactionId($adjustment->transactionId)
      ->setMetaPaddleAdjustmentId($adjustment->id);
    $refund = $this->fillRefundFromAdjustment($refund, $adjustment);
    $refund->save();
    $this->result
      ->setRefund($refund)
      ->appendMessage("refund {$refund->id} created from adjustment {$adjustment->id}", location: __FUNCTION__);

    PaddleMap::createOrUpdate($adjustment->id, Refund::class, $refund->id);
    return $refund;
  }

  /**
   * Update refund from adjustment
   *
   * if not force update and $adjustment is not newer, just return the $refund
   *
   * @param Refund $refund the refund to be updated
   * @param PaddleAdjustment $adjustment the corresponding adjustment
   * @param bool $force force update
   *
   * @return Refund
   */
  public function updateRefund(Refund $refund, PaddleAdjustment $adjustment, bool $force = false): Refund
  {
    // if not force update and $adjustment is not newer, just return $refund
    if (
      !$force &&
      $refund->getMeta()->paddle->paddle_timestamp &&
      Carbon::parse($refund->getMeta()->paddle->paddle_timestamp)->gte(
        ($adjustment->updatedAt ?? $adjustment->createdAt)->format('Y-m-d\TH:i:s\Z')
      )
    ) {
      $this->result->appendMessage("update skipped because it is up-to-date", location: __FUNCTION__);
      return $refund;
    }

    $refund = $this->fillRefundFromAdjustment($refund, $adjustment);
    $refund->save();
    $this->result->appendMessage("refund {$refund->id} updated from adjustment {$adjustment->id}", location: __FUNCTION__);

    return $refund;
  }

  /**
   * internal function to fill refund from adjustment (NOTE: do not save the refund)
   *
   * @param Refund $refund the refund to be filled
   * @param PaddleAdjustment $adjustment the corresponding adjustment
   *
   * @return Refund
   */
  public function fillRefundFromAdjustment(Refund $refund, PaddleAdjustment $adjustment): Refund
  {
    $refund->amount = CurrencyHelper::getDecimalPrice($adjustment->currencyCode->getValue(), $adjustment->totals->total);
    $refund->reason = $adjustment->reason;
    $refund->setMetaPaddleTimestamp($adjustment->updatedAt->format('Y-m-d\TH:i:s\Z'));

    if ($adjustment->status == AdjustmentStatus::PendingApproval()) {
      $status = Refund::STATUS_PENDING;
    } else if ($adjustment->status == AdjustmentStatus::Approved()) {
      $status = Refund::STATUS_COMPLETED;
    } else {
      // AdjustmentStatus::Rejected() or AdjustmentStatus::Reverse()
      $status = Refund::STATUS_FAILED;
    }
    $refund->setStatus($status);
    return $refund;
  }
}
