<?php

namespace App\Services\Paddle;

use App\Events\SubscriptionOrderEvent;
use App\Models\BillingInfo;
use App\Models\Invoice;
use App\Models\PaddleMap;
use App\Models\PaymentMethod;
use App\Models\ProductItem;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CurrencyHelper;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use App\Services\DigitalRiver\WebhookException;
use Illuminate\Support\Carbon;
use Paddle\SDK\Entities\Shared\TransactionOrigin;
use Paddle\SDK\Entities\Transaction as PaddleTransaction;
use Paddle\SDK\Notifications\Entities\Shared\TransactionOrigin as NotificationTransactionOrigin;
use Paddle\SDK\Notifications\Entities\Transaction as NotificationPaddleTransaction;
use Paddle\SDK\Notifications\Events\TransactionCompleted;
use Paddle\SDK\Notifications\Events\TransactionPastDue;

class TransactionService extends PaddleEntityService
{
  public function updateBillingInfo(BillingInfo $billingInfo, PaddleTransaction $paddleTransaction): BillingInfo
  {
    $this->result->appendMessage("updating billing info", location: __FUNCTION__);
    $this->manager->addressService->updateBillingInfo($billingInfo, $paddleTransaction->address);
    if ($paddleTransaction->business) {
      $this->manager->businessService->updateBillingInfo($billingInfo, $paddleTransaction->business);
    }
    return $billingInfo;
  }

  public function createOrUpdatePaymentMethod(User $user, PaddleTransaction|NotificationPaddleTransaction $paddleTransaction): PaymentMethod
  {
    if (!empty($paddleTransaction->payments)) {
      $this->result->appendMessage("updating user payment method", location: __FUNCTION__);
      return $this->manager->paymentMethodService->createOrUpdatePaymentMethod($user, $paddleTransaction->payments[0]);
    } else {
      $this->result->appendMessage("no payment found in transaction. skip updating.", location: __FUNCTION__);
      return $user->payment_method;
    }
  }

  public function updateSubscription(Subscription $subscription): Subscription
  {
    $this->result->appendMessage("updating subscription's billing info and payment method", location: __FUNCTION__);

    $user = $subscription->user;
    $subscription->payment_method_info = $user->payment_method->info();
    $subscription->billing_info = $user->billing_info->info();
    $subscription->save();
    return $subscription;
  }

  public function createOrUpdateInvoice(Subscription $subscription, PaddleTransaction $paddleTransaction): Invoice
  {
    /**
     * Steps
     * 1. find subscription by paddle subscription id
     * 2. if subscription not found, throw exception
     * 5. update billing info
     * 6. update payment method
     * 3. find or create invoice
     * 4. fill invoice from paddle transaction
     * 7. save invoice
     * 8. update subscription->payment_method / billing_info
     * 9. save
     */

    // find or create invoice
    $invoice = PaddleMap::findInvoiceByPaddleId($paddleTransaction->id);
    if ($invoice) {
      $this->result->appendMessage("updating invoice for paddle transaction ({$paddleTransaction->id})", location: __FUNCTION__);
      // check paddle_timestamp
    } else {
      $invoice =  (new Invoice())->setStatus(Invoice::STATUS_INIT);
      $this->result->appendMessage("creating invoice for paddle transaction ({$paddleTransaction->id})", location: __FUNCTION__);
    }


    if (
      $paddleTransaction->origin == TransactionOrigin::Web() ||
      $paddleTransaction->origin == TransactionOrigin::Api()
    ) {
      $invoice->setType(Invoice::TYPE_NEW_SUBSCRIPTION);
    } else if ($paddleTransaction->origin == TransactionOrigin::SubscriptionRecurring()) {
      $invoice->setType(Invoice::TYPE_RENEW_SUBSCRIPTION);
    } else if ($paddleTransaction->origin == TransactionOrigin::SubscriptionUpdate()) {
      $invoice->setType(Invoice::TYPE_UPDATE_SUBSCRIPTION);
    } else {
      $this->result->appendMessage("unsupported transaction origin: {$paddleTransaction->origin}", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    // fill invoice
    $invoice->user_id = $subscription->user_id;
    $invoice->subscription_id = $subscription->id;

    $invoice->currency = $paddleTransaction->currencyCode->getValue();

    // billing info: we assume it has been updated
    $invoice->billing_info = $subscription->user->billing_info->info();

    // payment method: we assume it has been updated
    $invoice->payment_method_info = $subscription->user->payment_method->info();

    // plan info, TODO: subscription may not be updated? maybe not important
    $invoice->plan_info = $subscription->plan->info($invoice->billing_info['address']['country']);

    // coupon info
    $coupon = $paddleTransaction->discount ? PaddleMap::findCouponByPaddleId($paddleTransaction->discount->id) : null;
    $invoice->coupon_info = $coupon?->info();

    // license package info
    $invoice->license_package_info = null;

    // items
    $invoice->items = ProductItem::buildItemsFromPaddleResource($paddleTransaction->details);

    $invoice->period = $subscription->current_period; // TODO: see subscription
    $invoice->period_start_date = $paddleTransaction->billingPeriod ? Carbon::parse($paddleTransaction->billingPeriod->startsAt) : null;
    $invoice->period_end_date   = $paddleTransaction->billingPeriod ?  Carbon::parse($paddleTransaction->billingPeriod->endsAt) : null;
    $invoice->invoice_date      = $paddleTransaction->billedAt ? Carbon::parse($paddleTransaction->billedAt) : null;

    // check
    $invoice->subtotal =      CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->subtotal);
    $invoice->discount =      CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->discount);
    $invoice->total_tax =     CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->tax);
    $invoice->total_amount =  CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->total);
    $invoice->total_refunded = 0; // TODO: ...

    $invoice->pdf_file = null;
    $invoice->credit_memos = null;

    $invoice->dr = [];
    $invoice->dr_invoice_id = null;
    $invoice->dr_order_id = null;
    $invoice->extra_data = null;

    $invoice->available_to_refund_amount = 0; // TODO: ...

    $invoice->setStatus(Invoice::STATUS_COMPLETED);
    $invoice->setSubStatus(Invoice::SUB_STATUS_NONE);
    $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_NONE);

    $invoice->setMetaPaddleCustomerId($paddleTransaction->customerId)
      ->setMetaPaddleSubscriptionId($paddleTransaction->subscriptionId)
      ->setMetaPaddleTransactionId($paddleTransaction->id)
      ->setMetaPaddleTimestamp($paddleTransaction->updatedAt->format('Y-m-d\TH:i:s\Z'));
    $invoice->save();

    PaddleMap::createOrUpdate($paddleTransaction->id, Invoice::class, $invoice->id);

    $this->result->appendMessage("invoice ({$invoice->id}) created / updated successfully", location: __FUNCTION__);
    return $invoice;
  }


  /**
   * transcation event handlers
   */

  public function onTransactionCompleted(TransactionCompleted $transactionCompleted)
  {
    // when it is a web transaction, this event will be skipped and invoice will be created by subscription service
    if ($transactionCompleted->transaction->origin->getValue() === NotificationTransactionOrigin::Web()->getValue()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("invoice is created from subscription.created event, skip this event", location: __FUNCTION__);
      return;
    }

    $subscription = $this->validateTransaction($transactionCompleted->transaction);

    $this->createOrUpdatePaymentMethod($subscription->user, $transactionCompleted->transaction);

    // payment method change event only update payment method
    if ($transactionCompleted->transaction->origin == NotificationTransactionOrigin::SubscriptionPaymentMethodChange()) {
      $this->updateSubscription($subscription);

      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
        ->appendMessage("update payment method completed", location: __FUNCTION__);
      return;
    }

    $this->result->appendMessage("retrieving paddle transaction for {$transactionCompleted->transaction->id}", location: __FUNCTION__);
    $paddleTransaction = $this->manager->paddleService->getTransaction($transactionCompleted->transaction->id);

    $this->updateBillingInfo($subscription->user->billing_info, $paddleTransaction);
    $this->updateSubscription($subscription);
    $invoice = $this->createOrUpdateInvoice($subscription, $paddleTransaction);

    SubscriptionOrderEvent::dispatch(SubscriptionOrderEvent::TYPE_ORDER_CONFIRMED, $invoice, null);


    $this->result
      ->setInvoice($invoice)
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("transaction completed event processed", location: __FUNCTION__);
  }

  public function onTransactionPastDue(TransactionPastDue $transactionPastDue)
  {
    if ($transactionPastDue->transaction->origin->getValue() !== NotificationTransactionOrigin::SubscriptionRecurring()->getValue()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("only origin == subscription recurring is supported", location: __FUNCTION__);
      return;
    }

    $subscription = $this->validateTransaction($transactionPastDue->transaction);

    // update payment method and billing info
    $this->createOrUpdatePaymentMethod($subscription->user, $transactionPastDue->transaction);

    $this->result->appendMessage("retrieving paddle transaction for {$transactionPastDue->transaction->id}", location: __FUNCTION__);
    $paddleTransaction = $this->manager->paddleService->getTransaction($transactionPastDue->transaction->id);

    $this->updateBillingInfo($subscription->user->billing_info, $paddleTransaction);
    $this->updateSubscription($subscription);
    $this->createOrUpdateInvoice($subscription, $paddleTransaction);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("transaction completed event processed", location: __FUNCTION__);
  }

  public function validateTransaction(PaddleTransaction|NotificationPaddleTransaction $paddleTransaction): Subscription
  {
    $paddleSubscriptionId = $paddleTransaction->subscriptionId;
    if (!$paddleSubscriptionId) {
      $this->result->appendMessage("paddle transaction ({$paddleTransaction->id}) does not have subscriptionId", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    $subscription = PaddleMap::findSubscriptionByPaddleId($paddleSubscriptionId);
    if (!$subscription) {
      $this->result->appendMessage("subscription not found for paddle subscription ({$paddleSubscriptionId})", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    $this->result->setSubscription($subscription);
    return $subscription;
  }

  public function getInvoicePdf(string $transactionId): string
  {
    return $this->manager->paddleService->getTransactionInvoicePdf($transactionId);
  }
}
