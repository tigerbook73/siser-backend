<?php

namespace App\Services\DigitalRiver;

use App\Models\Subscription;

class SubscriptionManager
{

  public function __construct()
  {
  }

  public function isSubscriptionLive(Subscription $subscription): bool
  {
    return true;
  }

  public function getSubscription($id): Subscription|null
  {
    return null;
  }

  public function getDraftSubscription($user_id): Subscription|null
  {
    return null;
  }

  public function getPendingSubscription($user_id): Subscription|null
  {
    return null;
  }

  public function getActiveSubscriptions($user_id): Subscription|null
  {
    return null;
  }

  public function getLiveSubscription($id): Subscription|null
  {
    return null;
  }

  public function getLiveSubscriptions($id): Subscription|null
  {
    return null;
  }

  public function createSubscription($toCreate): Subscription
  {
    return new Subscription();
  }

  public function deleteSubscription(Subscription $subscription): void
  {
    $subscription->delete();
  }

  public function updateSubscription(Subscription $subscription, $toUpdate): Subscription
  {
    return $subscription;
  }

  public function paySubscription(Subscription $subscription): Subscription
  {
    return $subscription;
  }

  public function activateSubscription(Subscription $subscription): Subscription
  {
    return $subscription;
  }

  public function cancelSubscription(Subscription $subscription): Subscription
  {
    return $subscription;
  }

  public function stopSubscription(Subscription $subscription, $reason): Subscription
  {
    return $subscription;
  }


  /**
   * order event
   * 
   * order.accepted                    -> onOrderAccepted() (order.status = accepted)
   * order.blocked                     -> onOrderFailed() (order.status = blocked)
   * order.charge.failed               -> onOrderFailed() (order.status = blocked)
   * order.cancelled                   -> onOrderCancelled() (order.status = cancelled)
   * order.pending_payment             -> log (order.status = pending_payment)
   * order.review_opened               -> log (order.status = in_review)
   * order.fulfilled                   -> log (order.status = fulfilled)
   * order.complete                    -> onOrderComplete() (order.status = complete)
   * order.invoice.created             -> onInvoiceCreated
   * order.credit_memo.created         -> onCreditMemoCreated // TODO:
   */

  public function onOrderAccepted()
  {
    // for the first order only
    // if not fulfilled, fulfill(order)
  }

  public function onOrderFailed()
  {
    // for the first order only
    // subscription.status = failed
  }

  public function onOrderCancelled()
  {
    // for the first order only
    // active subscription (dr & local)
  }

  public function onOrderCompleted()
  {
    // for the first order only
    // active subscription (dr & local)
  }

  /**
   * subscription event
   * 
   * subscription.created               -> onSubscriptionCreated()
   * subscription.deleted               -> log()
   * subscription.extended              -> onSubscriptionExtended()
   * subscription.failed                -> onSubscriptionFailed()
   * subscription.payment_failed        -> onSubscrptionPaymentFailed()
   * subscription.reminder              -> onSubscriptionReminder()
   * subscription.updated               -> log()
   */

  public function onSubscriptionCreated()
  {
    // validate subscription is created

    // if not , create ??
  }

  public function onSubscriptionExtended()
  {
    // update subscription data

    // notification customer (extented and next invoice date)
    // invoice (totalAmount, totalTax)
  }

  public function onSubscrptionPaymentFailed()
  {
    // notify the customer
    // credit card info
    // ask user to check their payment method
  }

  public function onSubscriptionFailed()
  {
    // update subscription status

    // notify the customer
  }

  public function onSubscriptionReminder()
  {
    // send reminder to customer

    // notifyu customer if credit card to be expired
  }
}
