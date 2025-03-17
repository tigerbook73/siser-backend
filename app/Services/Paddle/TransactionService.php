<?php

namespace App\Services\Paddle;

use App\Events\SubscriptionOrderEvent;
use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LicensePackage;
use App\Models\Paddle\PriceCustomData;
use App\Models\PaddleMap;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CurrencyHelper;
use App\Services\SubscriptionManager\SubscriptionManagerResult;
use App\Services\SubscriptionManager\WebhookException;
use Illuminate\Support\Carbon;
use Paddle\SDK\Entities\Shared\AdjustmentStatus;
use Paddle\SDK\Entities\Shared\TransactionOrigin;
use Paddle\SDK\Entities\Shared\TransactionStatus;
use Paddle\SDK\Entities\Transaction as PaddleTransaction;
use Paddle\SDK\Notifications\Entities\Shared\TransactionOrigin as NotificationTransactionOrigin;
use Paddle\SDK\Notifications\Entities\Transaction as NotificationPaddleTransaction;
use Paddle\SDK\Notifications\Events\TransactionCanceled;
use Paddle\SDK\Notifications\Events\TransactionCompleted;
use Paddle\SDK\Notifications\Events\TransactionPastDue;

class TransactionService extends PaddleEntityService
{
  /**
   * update billing info (address, business) from paddle transaction
   *
   * @param BillingInfo $billingInfo
   * @param PaddleTransaction $paddleTransaction
   *
   * @return BillingInfo
   */
  public function updateBillingInfo(BillingInfo $billingInfo, PaddleTransaction $paddleTransaction): BillingInfo
  {
    $this->result->appendMessage("updating billing info", location: __FUNCTION__);
    $this->manager->addressService->updateBillingInfo($billingInfo, $paddleTransaction->address);
    if ($paddleTransaction->business) {
      $this->manager->businessService->updateBillingInfo($billingInfo, $paddleTransaction->business);
    }
    return $billingInfo;
  }

  /**
   * create or update payment method from paddle transaction
   *
   * @param User $user
   * @param PaddleTransaction|NotificationPaddleTransaction $paddleTransaction
   *
   * @return PaymentMethod
   */
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

  /**
   * update subscription's billing info and payment method.
   * NOTE: this method shall be called after payment method is updated
   *
   * @param Subscription $subscription
   *
   * @return Subscription
   */
  public function updateSubscription(Subscription $subscription): Subscription
  {
    $this->result->appendMessage("updating subscription's billing info and payment method", location: __FUNCTION__);

    $user = $subscription->user;
    $subscription->setPaymentMethodInfo($user->payment_method->info());
    $subscription->setBillingInfo($user->billing_info->info());
    $subscription->save();
    return $subscription;
  }

  /**
   * create or update invoice from paddle transaction
   *
   * @param Subscription $subscription
   * @param PaddleTransaction $paddleTransaction
   *
   * @return Invoice
   */
  public function createOrUpdateInvoice(Subscription $subscription, PaddleTransaction $paddleTransaction): Invoice
  {
    // find or create invoice
    $invoice = PaddleMap::findInvoice($paddleTransaction->id);
    if ($invoice) {
      $this->result
        ->setInvoice($invoice)
        ->appendMessage("updating invoice for paddle transaction ({$paddleTransaction->id})", location: __FUNCTION__);
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
    $invoice->setBillingInfo($subscription->user->billing_info->info());

    // payment method: we assume it has been updated
    $invoice->setPaymentMethodInfo($subscription->user->payment_method->info());

    // plan info, TODO: subscription may not be updated? maybe not important
    $invoice->setPlanInfo($subscription->plan->info($invoice->getBillingInfo()->address->country));

    // coupon info
    $coupon = $paddleTransaction->discount ? PaddleMap::findCoupon($paddleTransaction->discount->id) : null;
    $invoice->setCouponInfo($coupon?->info());

    // license package info: only set when invoice is created
    if (!$invoice->exists()) {
      $priceCustomData = PriceCustomData::from($paddleTransaction->items[0]->price->customData?->data);
      if ($priceCustomData->license_quantity < LicensePackage::MIN_QUANTITY) {
        $invoice->setLicensePackageInfo(null);
      } else {
        $licensePackage = LicensePackage::findById($priceCustomData->license_package_id);
        $invoice->setLicensePackageInfo($licensePackage->info($priceCustomData->license_quantity));
      }
    }

    // items
    $invoice->setItems(InvoiceItem::buildItems($paddleTransaction));

    // period
    if ($paddleTransaction->billingPeriod) {
      $invoice->period = PeriodHelper::calcCurrentPeriod(
        $subscription->getPlanInfo()->interval,
        $subscription->getPlanInfo()->interval_count ?? 1,
        $subscription->start_date,
        $paddleTransaction->billingPeriod->startsAt
      );
      $invoice->period_start_date = Carbon::parse($paddleTransaction->billingPeriod->startsAt);
      $invoice->period_end_date   = Carbon::parse($paddleTransaction->billingPeriod->endsAt);
    } else {
      $invoice->period = 0;
      $invoice->period_start_date = null;
      $invoice->period_end_date = null;
    }
    $invoice->invoice_date = Carbon::parse($paddleTransaction->billedAt);

    // prices
    $invoice->subtotal          = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->subtotal);
    $invoice->discount          = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->discount);
    $invoice->total_tax         = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->tax);
    $invoice->total_amount      = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->total);
    $invoice->credit            = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->credit);
    $invoice->grand_total       = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->grandTotal);
    $invoice->credit_to_balance = CurrencyHelper::getDecimalPrice($invoice->currency, $paddleTransaction->details->totals->creditToBalance);

    // update refunded
    if ($paddleTransaction->adjustmentsTotals) {
      $invoice->total_refunded = CurrencyHelper::getDecimalPrice(
        $paddleTransaction->adjustmentsTotals->currencyCode->getValue(),
        $paddleTransaction->adjustmentsTotals->total
      );
    } else {
      $invoice->total_refunded = 0;
    }
    $invoice->available_to_refund_amount = $invoice->total_amount - $invoice->total_refunded;

    $invoice->pdf_file = null;
    $invoice->credit_memos = null;

    $invoice->dr = [];


    if ($paddleTransaction->status == TransactionStatus::Completed() || $paddleTransaction->status == TransactionStatus::Paid()) {
      if ($invoice->total_amount > 0 && $invoice->total_amount - $invoice->total_refunded < 0.005) {
        $invoice->setStatus(Invoice::STATUS_REFUNDED);
      } else {
        if (
          !empty($paddleTransaction->adjustments) &&
          !empty(array_filter($paddleTransaction->adjustments, fn($item) => $item->status == AdjustmentStatus::PendingApproval()))
        ) {
          $invoice->setStatus(Invoice::STATUS_REFUNDING);
        } else if ($invoice->total_refunded > 0) {
          $invoice->setStatus(Invoice::STATUS_PARTLY_REFUNDED);
        } else {
          $invoice->setStatus(Invoice::STATUS_COMPLETED);
        }
      }
    } else if ($paddleTransaction->status == TransactionStatus::PastDue()) {
      $invoice->setStatus(Invoice::STATUS_PENDING);
    } else if ($paddleTransaction->status == TransactionStatus::Canceled()) {
      $invoice->setStatus(Invoice::STATUS_CANCELLED);
    } else {
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    $invoice->setSubStatus(Invoice::SUB_STATUS_NONE);
    $invoice->setDisputeStatus(Invoice::DISPUTE_STATUS_NONE);

    $invoice->setMetaPaddleCustomerId($paddleTransaction->customerId)
      ->setMetaPaddleSubscriptionId($paddleTransaction->subscriptionId)
      ->setMetaPaddleTransactionId($paddleTransaction->id)
      ->setMetaPaddleTimestamp($paddleTransaction->updatedAt->format('Y-m-d\TH:i:s\Z'));
    $invoice->save();
    $this->result
      ->setInvoice($invoice)
      ->appendMessage("invoice ({$invoice->id}) saved", location: __FUNCTION__);

    PaddleMap::createOrUpdate($paddleTransaction->id, Invoice::class, $invoice->id, $paddleTransaction->customData?->data);

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

    $this->result->appendMessage("retrieving paddle transaction for {$transactionCompleted->transaction->id}", location: __FUNCTION__);
    $paddleTransaction = $this->paddleService->getTransaction($transactionCompleted->transaction->id);

    $this->createOrUpdatePaymentMethod($subscription->user, $paddleTransaction);

    // payment method change event only update payment method
    if ($paddleTransaction->origin == TransactionOrigin::SubscriptionPaymentMethodChange()) {
      $this->updateSubscription($subscription);

      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
        ->appendMessage("update payment method completed", location: __FUNCTION__);
      return;
    }


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

    $this->result->appendMessage("retrieving paddle transaction for {$transactionPastDue->transaction->id}", location: __FUNCTION__);
    $paddleTransaction = $this->paddleService->getTransaction($transactionPastDue->transaction->id);

    // update payment method and billing info
    $this->createOrUpdatePaymentMethod($subscription->user, $paddleTransaction);

    $this->updateBillingInfo($subscription->user->billing_info, $paddleTransaction);
    $this->updateSubscription($subscription);
    $this->createOrUpdateInvoice($subscription, $paddleTransaction);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("transaction past-due event processed", location: __FUNCTION__);
  }

  public function onTransactionCancelled(TransactionCanceled $transactionCanceled)
  {
    if ($transactionCanceled->transaction->origin->getValue() === TransactionOrigin::SubscriptionPaymentMethodChange()->getValue()) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage("skip subscription_payment_method_change transation.cancellation notification", location: __FUNCTION__);
      return;
    }

    $subscription = $this->validateTransaction($transactionCanceled->transaction);

    $this->result->appendMessage("retrieving paddle transaction for {$transactionCanceled->transaction->id}", location: __FUNCTION__);
    $paddleTransaction = $this->paddleService->getTransaction($transactionCanceled->transaction->id);

    $this->createOrUpdateInvoice($subscription, $paddleTransaction);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("transaction cancelled event processed", location: __FUNCTION__);
  }

  public function validateTransaction(PaddleTransaction|NotificationPaddleTransaction $paddleTransaction): Subscription
  {
    $paddleSubscriptionId = $paddleTransaction->subscriptionId;
    if (!$paddleSubscriptionId) {
      $this->result->appendMessage("paddle transaction ({$paddleTransaction->id}) does not have subscriptionId", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    $subscription = PaddleMap::findSubscription($paddleSubscriptionId);
    if (!$subscription) {
      $this->result->appendMessage("subscription not found for paddle subscription ({$paddleSubscriptionId})", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    $this->result->setSubscription($subscription);
    return $subscription;
  }

  public function getInvoicePdf(string $transactionId): string
  {
    return $this->paddleService->getTransactionInvoicePdf($transactionId);
  }

  public function refreshInvoice(Invoice $invoice): Invoice
  {
    // skip none-paddle transaction
    if (!$invoice->getMeta()->paddle->transaction_id) {
      $this->result->appendMessage("invoice ({$invoice->id}) does not have paddle transaction id", location: __FUNCTION__);
      return $invoice;
    }

    $paddleTransaction = $this->paddleService->getTransaction($invoice->getMeta()->paddle->transaction_id);
    return $this->createOrUpdateInvoice($invoice->subscription, $paddleTransaction);
  }
}
