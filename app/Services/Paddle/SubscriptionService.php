<?php

namespace App\Services\Paddle;

use App\Models\Coupon;
use App\Models\InvoiceItem;
use App\Models\LicensePackage;
use App\Models\Paddle\PriceCustomData;
use App\Models\Paddle\SubscriptionCustomData;
use App\Models\PaddleMap;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionNextInvoice;
use App\Services\CurrencyHelper;
use App\Services\SubscriptionManager\SubscriptionManagerResult;
use App\Services\SubscriptionManager\WebhookException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Paddle\SDK\Entities\Subscription as PaddleSubscription;
use Paddle\SDK\Entities\Subscription\SubscriptionScheduledChangeAction;
use Paddle\SDK\Entities\Subscription\SubscriptionStatus;
use Paddle\SDK\Entities\Transaction as PaddleTransaction;
use Paddle\SDK\Notifications\Entities\Subscription as NotificationPaddleSubscription;
use Paddle\SDK\Notifications\Events\SubscriptionCreated;
use Paddle\SDK\Notifications\Events\SubscriptionUpdated;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionService extends PaddleEntityService
{
  public function createSubscription(PaddleSubscription $paddleSubscription, PaddleTransaction $paddleTransaction): Subscription
  {
    $subscription = (new Subscription([
      'user_id' => SubscriptionCustomData::from($paddleSubscription->customData?->data)->user_id,
    ]))->initFill();

    // if user already has active live subscription, throw exception
    if ($subscription->user->getActiveLiveSubscription()) {
      $this->result->appendMessage("user {$subscription->user_id} already has active live subscription", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    // stop active subscription if any
    $previousSubscription = $subscription->user->getActiveSubscription();
    if ($previousSubscription) {
      $this->result->appendMessage("stopping previous subscription ({$previousSubscription->id})", location: __FUNCTION__);
      $previousSubscription->stop(Subscription::STATUS_STOPPED, 'new subscription activated');

      // if it's a paddle subscription, warning!!
      if ($previousSubscription->getMeta()->paddle->subscription_id) {
        Log::error("paddle subscription ({$previousSubscription->id}) is stopped by new paddle subscription ({$paddleSubscription->id})");
      }
    }

    // fill subscription and save
    $this->result->appendMessage("filling subscription ({$subscription->id}) from ({$paddleSubscription->id})", location: __FUNCTION__);
    $this->fillSubscription($subscription, $paddleSubscription, $paddleTransaction);

    $this->result->appendMessage("saving subscription ({$subscription->id})", location: __FUNCTION__);
    $subscription->save();

    $this->result->appendMessage("refreshing license sharing for subscription ({$subscription->id})", location: __FUNCTION__);
    $this->refreshLicenseSharing($subscription);

    PaddleMap::createOrUpdate($paddleSubscription->id, Subscription::class, $subscription->id, $paddleSubscription->customData?->data);

    $this->result->appendMessage("subscription ({$subscription->id}) created successfully", location: __FUNCTION__);
    return $subscription;
  }

  public function updateSubscription(Subscription $subscription, PaddleSubscription $paddleSubscription, ?PaddleTransaction $paddleTransaction = null): Subscription
  {
    $this->result->appendMessage("filling subscription ({$subscription->id}) from ({$paddleSubscription->id})", location: __FUNCTION__);
    $this->fillSubscription($subscription, $paddleSubscription, $paddleTransaction);

    $this->result->appendMessage("saving subscription ({$subscription->id})", location: __FUNCTION__);
    $subscription->save();

    $this->result->appendMessage("refreshing license sharing for subscription ({$subscription->id})", location: __FUNCTION__);
    $this->refreshLicenseSharing($subscription);

    PaddleMap::createOrUpdate($paddleSubscription->id, Subscription::class, $subscription->id, $paddleSubscription->customData?->data);

    $this->result->appendMessage("subscription ({$subscription->id}) updated successfully", location: __FUNCTION__);
    return $subscription;
  }

  public function refreshLicenseSharing(Subscription $subscription): Subscription
  {
    $licenseSharing = $subscription->user->getActiveLicenseSharing();
    if ($licenseSharing) {
      $this->result->appendMessage("refreshing license sharing for subscription ({$subscription->id})", location: __FUNCTION__);
      $this->manager->licenseService->refreshLicenseSharing($licenseSharing);
    } else if (
      $subscription->getStatus() == Subscription::STATUS_ACTIVE &&
      $subscription->hasLicensePackageInfo()
    ) {
      $this->result->appendMessage("creating license sharing for subscription ({$subscription->id})", location: __FUNCTION__);
      $this->licenseService->createLicenseSharing($subscription);
    } else {
      $this->result->appendMessage("updating user ({$subscription->user_id}) subscription level", location: __FUNCTION__);
      $subscription->user->updateSubscriptionLevel();
    }
    return $subscription->refresh();
  }

  /**
   * Fill subscription from Paddle Subscription and Paddle Transaction (optional) without saving
   */
  public function fillSubscription(
    Subscription $subscription,
    PaddleSubscription $paddleSubscription,
    ?PaddleTransaction $paddleTransaction
  ): Subscription {
    // custom data
    $subscriptionCustomerData = SubscriptionCustomData::from($paddleSubscription->customData?->data);

    // user id
    $subscription->user_id      = $subscriptionCustomerData->user_id;

    // plan id (never change)
    $subscription->plan_id      = $subscriptionCustomerData->plan_id;

    // currency
    $subscription->currency     = $paddleSubscription->currencyCode->getValue();

    //
    // prices: always be the recurring prices without considering the discount (unless it is a permanent discount)
    //
    $recurringTransactionDetails = $paddleSubscription->recurringTransactionDetails;
    if ($recurringTransactionDetails) {
      // purchase subscription and renewal scenario
      $subscription->price        = CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $recurringTransactionDetails->totals->subtotal);
      $subscription->subtotal     = CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $recurringTransactionDetails->totals->subtotal);
      $subscription->discount     = CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $recurringTransactionDetails->totals->discount);
      $subscription->total_amount = CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $recurringTransactionDetails->totals->total);
      $subscription->total_tax    = CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $recurringTransactionDetails->totals->tax);
      $subscription->tax_rate     = (float)($recurringTransactionDetails->taxRatesUsed[0]->taxRate);
    }

    // start & end data
    if (!$paddleSubscription->startedAt) {
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }
    $subscription->start_date   = Carbon::parse($paddleSubscription->startedAt);
    if ($paddleSubscription->canceledAt) {
      $subscription->end_date = Carbon::parse($paddleSubscription->canceledAt);
    } else if (($paddleSubscription->scheduledChange?->action == SubscriptionScheduledChangeAction::Cancel())) {
      $subscription->end_date = Carbon::parse($paddleSubscription->scheduledChange->effectiveAt);
    } else {
      $subscription->end_date = null;
    }

    // current billing period
    if ($paddleSubscription->currentBillingPeriod) {
      $subscription->current_period = PeriodHelper::calcCurrentPeriod(
        $paddleSubscription->billingCycle->interval->getValue(),
        $paddleSubscription->billingCycle->frequency,
        $paddleSubscription->startedAt,
        $paddleSubscription->currentBillingPeriod->startsAt,
      );

      // current period start & end date
      $subscription->current_period_start_date = Carbon::parse($paddleSubscription->currentBillingPeriod->startsAt);
      $subscription->current_period_end_date = Carbon::parse($paddleSubscription->currentBillingPeriod->endsAt);
    }

    // next invoice date & reminder date
    if ($paddleSubscription->nextBilledAt) {
      $subscription->next_invoice_date  = Carbon::parse($paddleSubscription->nextBilledAt);
      $subscription->next_reminder_date = Carbon::parse($paddleSubscription->nextBilledAt)->subDays(3); // hard code
    } else {
      $subscription->next_invoice_date  = null;
      $subscription->next_reminder_date = null;
    }

    // update billing info and generate billing info.
    if ($this->isPaddleSubscriptionActive($paddleSubscription) && $paddleTransaction) {
      $billingInfo = $subscription->user->billing_info;
      $this->manager->addressService->updateBillingInfo($billingInfo, $paddleTransaction->address);
      if ($paddleTransaction->business) {
        $this->manager->businessService->updateBillingInfo($billingInfo, $paddleTransaction->business);
      }
      $subscription->setBillingInfo($billingInfo->info());
    } else {
      // keep current data
    }

    // update payment method and generate payment method info
    if ($this->isPaddleSubscriptionActive($paddleSubscription) && !empty($paddleTransaction?->payments)) {
      $paymentMethod = $this->manager->paymentMethodService->createOrUpdatePaymentMethod(
        $subscription->user,
        $paddleTransaction->payments[0] // most recent payment
      );
      $subscription->setPaymentMethodInfo($paymentMethod->info());
    } else {
      // keep current data
    }

    // plan info: license package is not considered, TODO: plan price change need to be considered
    $subscription->setPlanInfo(
      $subscription->plan->info($subscription->user->billing_info->address()->country)
    );

    // subscription level
    $subscription->subscription_level = $subscription->plan->subscription_level;

    // license package info: only set when subscription is created or license package number was changed
    $priceCustomData = PriceCustomData::from($paddleSubscription->items[0]->price->customData?->data);
    if (
      !$subscription->exists() ||
      ($subscription->getLicensePackageInfo()?->price_rate->quantity ?? PriceCustomData::DEFAULT_QUANTITY) != $priceCustomData->license_quantity
    ) {
      if ($priceCustomData->license_quantity < LicensePackage::MIN_QUANTITY) {
        $subscription->setLicensePackageInfo(null);
      } else {
        $licensePackage = LicensePackage::findById($priceCustomData->license_package_id);
        $subscription->setLicensePackageInfo($licensePackage->info($priceCustomData->license_quantity));
      }
    }

    // coupon info
    $coupon = ($paddleSubscription->discount?->startsAt) ?
      PaddleMap::findCoupon($paddleSubscription->discount->id) :
      null;
    if (
      $coupon &&
      $paddleSubscription->discount->startsAt < Carbon::parse($paddleSubscription->currentBillingPeriod?->endsAt)->subSeconds(60) &&
      (!$paddleSubscription->discount->endsAt ||
        $paddleSubscription->discount->endsAt > Carbon::parse($paddleSubscription->currentBillingPeriod?->endsAt)->subSeconds(60))
    ) {
      $subscription->coupon_id   = $coupon->id;
      $subscription->setCouponInfo($coupon->info());
    } else {
      $subscription->coupon_id   = null;
      $subscription->setCouponInfo(null);
    }

    // items
    $subscription->setItems(SubscriptionItem::buildItems($paddleSubscription));

    // next_invoice
    $nextTransaction  = $paddleSubscription->nextTransaction;
    if ($nextTransaction) {
      $billingPeriod    = $nextTransaction->billingPeriod;
      $currency         = $nextTransaction->details->totals->currencyCode->getValue();
      $totals           = $nextTransaction->details->totals;
      $taxRate          = (float)($nextTransaction->details->taxRatesUsed[0]->taxRate);
      $couponInfo       = (
        $coupon &&
        $paddleSubscription->discount->startsAt < Carbon::parse($billingPeriod->startsAt)->addSeconds(60) &&
        (!$paddleSubscription->discount->endsAt ||
          $paddleSubscription->discount->endsAt > Carbon::parse($billingPeriod->endsAt)->subSeconds(60))
      ) ?
        $coupon->info() :
        null;

      $subscription->setNextInvoice(new SubscriptionNextInvoice(
        current_period: $subscription->current_period + 1,
        current_period_start_date: Carbon::parse($billingPeriod->startsAt),
        current_period_end_date: Carbon::parse($billingPeriod->endsAt),
        plan_info: $subscription->getPlanInfo(),
        coupon_info: $couponInfo,
        license_package_info: $subscription->getLicensePackageInfo(),
        items: InvoiceItem::buildNextItemsForSubscription($paddleSubscription),
        price: CurrencyHelper::getDecimalPrice($currency, $totals->subtotal),
        subtotal: CurrencyHelper::getDecimalPrice($currency, $totals->subtotal),
        discount: CurrencyHelper::getDecimalPrice($currency, $totals->discount),
        tax_rate: (float)$taxRate,
        total_tax: CurrencyHelper::getDecimalPrice($currency, $totals->tax),
        total_amount: CurrencyHelper::getDecimalPrice($currency, $totals->total),
        credit: CurrencyHelper::getDecimalPrice($currency, $totals->credit),
        credit_to_balance: CurrencyHelper::getDecimalPrice($currency, $totals->creditToBalance),
        grand_total: CurrencyHelper::getDecimalPrice($currency, $totals->grandTotal),
      ));
    } else {
      $subscription->setNextInvoice(null);
    }

    // other info
    $subscription->stop_reason = '';

    // meta
    $subscription->setMetaPaddleSubscriptionId($paddleSubscription->id)
      ->setMetaPaddleCustomerId($paddleSubscription->customerId)
      ->setMetaPaddleProductId($paddleSubscription->items[0]->product->id)
      ->setMetaPaddlePriceId($paddleSubscription->items[0]->price->id)
      ->setMetaPaddleDiscountId($paddleSubscription->discount?->id)
      ->setMetaPaddleTimestamp($paddleSubscription->updatedAt->format('Y-m-d\TH:i:s\Z'));

    // status
    if ($this->isPaddleSubscriptionActive($paddleSubscription)) {
      $status = Subscription::STATUS_ACTIVE;
      $subStatus =
        $paddleSubscription->scheduledChange?->action == SubscriptionScheduledChangeAction::Cancel() ?
        Subscription::SUB_STATUS_CANCELLING :
        Subscription::SUB_STATUS_NORMAL;
    } else {
      $status = Subscription::STATUS_STOPPED;
      $subStatus = Subscription::SUB_STATUS_NORMAL;
    }
    if ($subscription->getStatus() !== $status) {
      $this->result->appendMessage("subscription ({$subscription->id}) status updated: {$subscription->getStatus()} => {$status}", location: __FUNCTION__);
      $subscription->setStatus($status);
    }
    if ($subscription->sub_status !== $subStatus) {
      $this->result->appendMessage("subscription ({$subscription->id}) sub_status updated: {$subscription->sub_status} => {$subStatus}", location: __FUNCTION__);
      $subscription->sub_status = $subStatus;
    }

    return $subscription;
  }

  public function onSubscriptionCreated(SubscriptionCreated $subscriptionCreated): void
  {
    $paddleSubscriptionNotification = $subscriptionCreated->subscription;
    $subscription = PaddleMap::findSubscription($paddleSubscriptionNotification->id);
    if ($subscription) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED, 'subscription already exists')
        ->appendMessage("subscription ({$subscription->id}) already exists for {$paddleSubscriptionNotification->id}", location: __FUNCTION__);
      return;
    }
    if (!$paddleSubscriptionNotification->transactionId) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED, 'no transactionId')
        ->appendMessage("paddle subscription ({$paddleSubscriptionNotification->id}) does not have transactionId", location: __FUNCTION__);
      return;
    }

    $this->result->appendMessage("retrieving paddle subscription for {$paddleSubscriptionNotification->id}", location: __FUNCTION__);
    $paddleSubscription = $this->paddleService->getSubscriptionWithIncludes($paddleSubscriptionNotification->id);

    $this->result->appendMessage("retrieving paddle transaction for {$paddleSubscriptionNotification->transactionId}", location: __FUNCTION__);
    $paddleTransaction = $this->paddleService->getTransaction($paddleSubscriptionNotification->transactionId);

    $this->result->appendMessage("creating subscription for {$paddleSubscription->id}", location: __FUNCTION__);
    $subscription = $this->createSubscription($paddleSubscription, $paddleTransaction);

    // create invoice immediately because transaction.completed event may come before subscription is created
    $this->result->appendMessage("createing transaction for {$paddleTransaction->id}", location: __FUNCTION__);
    $invoice = $this->manager->transactionService->createOrUpdateInvoice($subscription, $paddleTransaction);

    $this->result
      ->setInvoice($invoice)
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("subscription ({$subscription->id}) for {$paddleSubscription->id} created", location: __FUNCTION__);
  }

  public function onSubscriptionUpdated(SubscriptionUpdated $subscriptionUpdated): void
  {
    // step 1: get notification.paddleSubscription
    $paddleSubscriptionNotification = $subscriptionUpdated->subscription;

    // step 2. if user_id is empty, skip
    if (!SubscriptionCustomData::from($paddleSubscriptionNotification->customData?->data)->user_id) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED, 'no user_id, not created from customer portal')
        ->appendMessage("paddle subscription ({$paddleSubscriptionNotification->id}) does not have user_id", location: __FUNCTION__);
      return;
    }

    // step 3: if subscription not exist, fails
    $subscription = PaddleMap::findSubscription($paddleSubscriptionNotification->id);
    if (!$subscription) {
      $this->result->appendMessage("subscription not found for paddle Subscription {$paddleSubscriptionNotification->id}", location: __FUNCTION__);
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }
    $this->result->setSubscription($subscription);

    // step 4 validate subscription.timestamp, if it's newer than the paddleSubscription.updated_at, skip
    if (
      $subscription->getMeta()->paddle->paddle_timestamp &&
      Carbon::parse($subscription->getMeta()->paddle->paddle_timestamp)->gte(
        $paddleSubscriptionNotification->updatedAt->format('Y-m-d\TH:i:s\Z')
      )
    ) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED, 'subscription already updated')
        ->appendMessage("subscription ({$subscription->id}) for ({$paddleSubscriptionNotification->id}) already updated", location: __FUNCTION__);
      return;
    }

    // step 5 if subscription.status is stopped or failed, skip
    if (
      $subscription->getStatus() == Subscription::STATUS_STOPPED ||
      $subscription->getStatus() == Subscription::STATUS_FAILED
    ) {
      $this->result->appendMessage("Subscription {$subscription->id} already stopped or failed", location: __FUNCTION__);
      return;
    }

    // step 6. update subscription from paddle
    $this->result->appendMessage("retrieving paddle subscription for {$paddleSubscriptionNotification->id}", location: __FUNCTION__);
    $paddleSubscription = $this->paddleService->getSubscriptionWithIncludes($paddleSubscriptionNotification->id);

    if ($paddleSubscriptionNotification->transactionId) {
      $this->result->appendMessage("retrieving paddle transaction for {$paddleSubscriptionNotification->transactionId}", location: __FUNCTION__);
      $paddleTransaction = $this->paddleService->getTransaction($paddleSubscriptionNotification->transactionId);
    } else {
      $paddleTransaction = null;
    }

    $this->result->appendMessage("updating subscription ({$subscription->id}) for {$paddleSubscription->id}", location: __FUNCTION__);
    $this->updateSubscription($subscription, $paddleSubscription, $paddleTransaction);

    $this->result
      ->setResult(SubscriptionManagerResult::RESULT_PROCESSED)
      ->appendMessage("subscription ({$subscription->id}) for {$paddleSubscription->id} updated", location: __FUNCTION__);
  }

  public function isPaddleSubscriptionActive(PaddleSubscription|NotificationPaddleSubscription $paddleSubscription): bool
  {
    $activeStatues = [
      SubscriptionStatus::Active()->getValue(),
      SubscriptionStatus::Trialing()->getValue(),
      SubscriptionStatus::PastDue()->getValue(),
    ];
    return in_array($paddleSubscription->status->getValue(), $activeStatues);
  }

  public function isPaddleSubscriptionStopped(PaddleSubscription|NotificationPaddleSubscription $paddleSubscription): bool
  {
    return $paddleSubscription->status->getValue() === SubscriptionStatus::Canceled()->getValue();
  }

  public function getManagementLinks(Subscription $subscription): array
  {
    if (
      !$subscription->isActive() ||
      !$subscription->getMeta()->paddle->subscription_id
    ) {
      throw new HttpException(400, 'Subscription is not active or not created from Paddle');
    }

    $paddleSubscription = $this->paddleService->getSubscription($subscription->getMeta()->paddle->subscription_id);

    return [
      'update_payment_method' => $paddleSubscription->managementUrls->updatePaymentMethod,
      'cancel' => $paddleSubscription->managementUrls->cancel,
    ];
  }

  public function cancelSubscription(Subscription $subscription, bool $immediate): Subscription
  {
    if (!$subscription->isActive() || !$subscription->isPaid()) {
      throw new WebhookException('Subscription is not active or not paid, cannot cancel', 400);
    }

    if ($subscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      throw new WebhookException('Subscription is already on cancelling', 400);
    }

    if (!$subscription->getMeta()->paddle->subscription_id) {
      throw new WebhookException('Subscription is not created from Paddle', 400);
    }

    /**
     * TODO: refund consideration
     */
    $paddleSubscription = $this->paddleService->cancelSubscription($subscription->getMeta()->paddle->subscription_id, $immediate);
    $this->result->appendMessage("paddle-subscription for subscription ({$subscription->id}) cancelled", location: __FUNCTION__);

    $this->updateSubscription($subscription, $paddleSubscription);
    return $subscription;
  }

  public function dontCancelSubscription(Subscription $subscription): Subscription
  {
    if ($subscription->sub_status !== Subscription::SUB_STATUS_CANCELLING) {
      throw new WebhookException('Subscription is not on cancelling', 400);
    }

    if (!$subscription->getMeta()->paddle->subscription_id) {
      throw new WebhookException('Subscription is not created from Paddle', 400);
    }

    $paddleSubscription = $this->paddleService->removeSubscriptionScheduledChange($subscription->getMeta()->paddle->subscription_id);
    $this->result->appendMessage("paddle-subscription for subscription ({$subscription->id}) sheduled cancellation removed", location: __FUNCTION__);

    $this->updateSubscription($subscription, $paddleSubscription);
    return $subscription;
  }

  public function refreshSubscription(Subscription $subscription): Subscription
  {
    $paddleSubscription = $this->paddleService->getSubscription($subscription->getMeta()->paddle->subscription_id);
    $this->updateSubscription($subscription, $paddleSubscription);
    return $subscription;
  }
}
