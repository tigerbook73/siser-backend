<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Configuration;
use App\Models\Subscription;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


class DigitalRiverService
{
  /** @var Client $client */
  public $client = null;

  /** @var \DigitalRiver\ApiSdk\Configuration DR configuration */
  public $config = null;

  /** @var \DigitalRiver\ApiSdk\Api\PlansApi|null */
  public $planApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\CustomersApi|null */
  public $customerApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\CheckoutsApi|null */
  public $checkoutApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\SubscriptionsApi|null */
  public $subscriptionApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\OrdersApi|null */
  public $orderApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\FulfillmentsApi|null */
  public $fulfillmentApi = null;

  /** @var \DigitalRiver\ApiSdk\Api\SourcesApi|null */
  public $sourceApi = null;

  public function __construct()
  {
    // rest api client
    $this->client = new Client();

    // DR configuration
    $this->config = \DigitalRiver\ApiSdk\Configuration::getDefaultConfiguration();
    $this->config->setAccessToken('sk_test_dc25334eed6a4fff8ad1516920379189');
    $this->config->setHost('https://api.digitalriver.com');

    // DR apis
    $this->planApi          = new \DigitalRiver\ApiSdk\Api\PlansApi($this->client, $this->config);
    $this->checkoutApi      = new \DigitalRiver\ApiSdk\Api\CheckoutsApi($this->client, $this->config);
    $this->subscriptionApi  = new \DigitalRiver\ApiSdk\Api\SubscriptionsApi($this->client, $this->config);
    $this->orderApi         = new \DigitalRiver\ApiSdk\Api\OrdersApi($this->client, $this->config);
    $this->fulfillmentApi   = new \DigitalRiver\ApiSdk\Api\FulfillmentsApi($this->client, $this->config);
    $this->customerApi      = new \DigitalRiver\ApiSdk\Api\CustomersApi($this->client, $this->config);
    $this->sourceApi        = new \DigitalRiver\ApiSdk\Api\SourcesApi($this->client, $this->config);
  }

  /**
   * helper function
   */
  protected function fillAddress(array $addr): \DigitalRiver\ApiSdk\Model\Address
  {
    $address = new \DigitalRiver\ApiSdk\Model\Address();
    $address->setLine1($addr['line1']);
    $address->setLine2($addr['line2']);
    $address->setCity($addr['city']);
    $address->setPostalCode($addr['postcode']);
    $address->setState($addr['state']);
    $address->setCountry($addr['country']);
    return $address;
  }

  protected function fillShipping(BillingInfo|array $billingInfo): \DigitalRiver\ApiSdk\Model\Shipping
  {
    $shipping = new \DigitalRiver\ApiSdk\Model\Shipping();
    if ($billingInfo instanceof BillingInfo) {
      $shipping->setAddress($this->fillAddress($billingInfo->address));
      $shipping->setName($billingInfo->first_name . ' ' . $billingInfo->last_name);
      $shipping->setPhone($billingInfo->phone);
      $shipping->setEmail($billingInfo->email);
      $shipping->setOrganization($billingInfo->organization);
    } else {
      $shipping->setAddress($this->fillAddress($billingInfo['address']));
      $shipping->setName($billingInfo['first_name'] . ' ' . $billingInfo['last_name']);
      $shipping->setPhone($billingInfo['phone']);
      $shipping->setEmail($billingInfo['email']);
      $shipping->setOrganization($billingInfo['organization']);
    }
    return $shipping;
  }

  protected function fillBilling(BillingInfo|array $billingInfo): \DigitalRiver\ApiSdk\Model\Billing
  {
    $billTo = new \DigitalRiver\ApiSdk\Model\Billing();
    if ($billingInfo instanceof BillingInfo) {
      $billTo->setName($billingInfo['first_name'] . ' ' . $billingInfo['first_name']);
      $billTo->setPhone($billingInfo['phone']);
      $billTo->setEmail($billingInfo['email']);
      $billTo->setOrganization($billingInfo['organization'] ?: null);
      $billTo->setAddress($this->fillAddress($billingInfo->address));
    } else {
      $billTo->setName($billingInfo['first_name'] . ' ' . $billingInfo['first_name']);
      $billTo->setPhone($billingInfo['phone']);
      $billTo->setEmail($billingInfo['email']);
      $billTo->setOrganization($billingInfo['organization'] ?: null);
      $billTo->setAddress($this->fillAddress($billingInfo['address']));
    }
    return $billTo;
  }

  protected function fillSubscriptionItemProductDetails(Subscription $subscription): \DigitalRiver\ApiSdk\Model\ProductDetails
  {
    $productDetails = new \DigitalRiver\ApiSdk\Model\ProductDetails();
    $productDetails->setSkuGroupId(config('dr.sku_grp_subscription'));
    $productName = $subscription->plan_info['name'] . ($subscription->coupon_info ? '(' . $subscription->coupon_info['code'] . ')' : '');
    $productDetails->setName($productName);
    $productDetails->setDescription("");
    $productDetails->setCountryOfOrigin('AU');

    return $productDetails;
  }

  protected function fillProcessingFeeItemProductDetails(Subscription $subscription): \DigitalRiver\ApiSdk\Model\ProductDetails
  {
    // productDetails
    $productDetails = new \DigitalRiver\ApiSdk\Model\ProductDetails();
    $productDetails->setSkuGroupId(config('dr.sku_grp_process_fee'));
    $productName = 'Processing fee (' . $subscription->processing_fee_info['processing_fee_rate'] . ')';
    $productDetails->setName($productName);
    $productDetails->setDescription('');
    $productDetails->setCountryOfOrigin('AU');

    return $productDetails;
  }


  /**
   * plan
   */
  public function getDefaultPlan(): \DigitalRiver\ApiSdk\Model\Plan|null
  {
    try {
      return $this->planApi->retrievePlans(config('dr.default_plan'));
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function createDefaultPlan(Configuration $configuration): \DigitalRiver\ApiSdk\Model\Plan|null
  {
    $planRequest = new \DigitalRiver\ApiSdk\Model\PlanRequest();

    $planRequest->setId(config('dr.default_plan'));
    $planRequest->setTerms('These are the terms...');
    $planRequest->setContractBindingDays(10000);
    $planRequest->setInterval('month');
    $planRequest->setIntervalCount(1);
    $planRequest->setName('default-plan');
    $planRequest->setReminderOffsetDays($configuration->plan_reminder_offset_days);
    $planRequest->setBillingOffsetDays($configuration->plan_billing_offset_days);
    $planRequest->setCollectionPeriodDays($configuration->plan_collection_period_days);
    $planRequest->setState('active');

    try {
      return $this->planApi->createPlans($planRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function UpdateDefaultPlan(Configuration $configuration): \DigitalRiver\ApiSdk\Model\Plan|null
  {
    $planRequest = new \DigitalRiver\ApiSdk\Model\UpdatePlanRequest();
    $planRequest->setReminderOffsetDays($configuration->plan_reminder_offset_days);
    $planRequest->setBillingOffsetDays($configuration->plan_billing_offset_days);
    $planRequest->setCollectionPeriodDays($configuration->plan_collection_period_days);

    try {
      return $this->planApi->updatePlans(config('dr.default_plan'), $planRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  /**
   * customer
   */
  public function getCustomer(string|int $id): \DigitalRiver\ApiSdk\Model\Customer|null
  {
    try {
      return $this->customerApi->retrieveCustomers((string)$id);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function createCustomer(BillingInfo $billingInfo): \DigitalRiver\ApiSdk\Model\Customer|null
  {
    $customerRequest = new \DigitalRiver\ApiSdk\Model\CustomerRequest();
    // $customerRequest->setId('customer-' . $billingInfo->user_id);
    $customerRequest->setEmail($billingInfo->email);
    $customerRequest->setShipping($this->fillShipping($billingInfo));
    $customerRequest->setMetadata(['user_id' => $billingInfo->user_id]);

    try {
      return $this->customerApi->createCustomers($customerRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function updateCustomer(BillingInfo $billingInfo): \DigitalRiver\ApiSdk\Model\Customer|null
  {
    $customerRequest = new \DigitalRiver\ApiSdk\Model\UpdateCustomerRequest();
    $customerRequest->setEmail($billingInfo->email);
    // $customerRequest->setShipping($this->fillShipping($billingInfo));
    // $customerRequest->setMetadata(['user_id' => $billingInfo->user_id]);

    try {
      return $this->customerApi->updateCustomers((string)$billingInfo->user_id, $customerRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function attachCustomerSource(string|int $customerId, string|int $source_id): \DigitalRiver\ApiSdk\Model\Source|null
  {
    try {
      return $this->customerApi->createCustomerSource((string)$customerId, (string)$source_id);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function dettachCustomerSource(string|int $customerId, string|int $source_id): bool
  {
    try {
      $this->customerApi->deleteCustomerSource((string)$customerId, (string)$source_id);
      return true;
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return false;
    }
  }

  /**
   * checkout
   */
  public function getCheckout(string|int $id): \DigitalRiver\ApiSdk\Model\Checkout|null
  {
    try {
      return $this->checkoutApi->retrieveCheckouts((string)$id);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }


  protected function fillCheckoutSubscriptionItem(Subscription $subscription): \DigitalRiver\ApiSdk\Model\SkuRequestItem
  {
    // productDetails
    $productDetails = $this->fillSubscriptionItemProductDetails($subscription);

    // subscriptionInfo
    $subscriptionInfo = new \DigitalRiver\ApiSdk\Model\SubscriptionInfo();
    $subscriptionInfo->setPlanId(config('dr.default_plan'));
    $subscriptionInfo->setTerms('These are the terms...');
    $subscriptionInfo->setAutoRenewal(true);

    // discount
    $discount = null;
    if ($subscription->coupon_info) {
      $discount = new \DigitalRiver\ApiSdk\Model\SkuDiscount();
      $discount->setPercentOff($subscription->coupon_info['percentage_off']);
    }

    // item
    $item = new \DigitalRiver\ApiSdk\Model\SkuRequestItem();
    $item->setProductDetails($productDetails);
    $item->setSubscriptionInfo($subscriptionInfo);
    $item->setPrice($subscription->price);
    $item->setQuantity(1);
    $item->setDiscount($discount);

    return $item;
  }

  protected function fillCheckoutProcessingFeeItem(Subscription $subscription): \DigitalRiver\ApiSdk\Model\SkuRequestItem
  {
    // productDetails
    $productDetails = $this->fillSubscriptionItemProductDetails($subscription);

    // subscriptionInfo
    $subscriptionInfo = new \DigitalRiver\ApiSdk\Model\SubscriptionInfo();
    $subscriptionInfo->setPlanId(config('dr.default_plan'));
    $subscriptionInfo->setTerms('These are the terms...');
    $subscriptionInfo->setAutoRenewal(true);

    // item
    $item = new \DigitalRiver\ApiSdk\Model\SkuRequestItem();
    $item->setProductDetails($productDetails);
    $item->setSubscriptionInfo($subscriptionInfo);
    $item->setPrice($subscription->processing_fee);
    $item->setQuantity(1);

    return $item;
  }

  protected function fillCheckoutItems(Subscription $subscription)
  {
    $items[] = $this->fillCheckoutSubscriptionItem($subscription);
    if ($subscription->processing_fee_info['explicit_processing_fee']) {
      $items[] = $this->fillCheckoutProcessingFeeItem($subscription);
    }
    return $items;
  }


  public function createCheckout(Subscription $subscription): \DigitalRiver\ApiSdk\Model\Checkout|null
  {
    // checkout
    $checkoutRequest = new \DigitalRiver\ApiSdk\Model\CheckoutRequest();
    $checkoutRequest->setCustomerId((string)$subscription->user_id);
    $checkoutRequest->setEmail($subscription->billing_info['email']);
    // $checkoutRequest->setLocale('string'); // TODO:
    $checkoutRequest->setBrowserIp(request()->ip());
    // $checkoutRequest->setTaxIdentifiers('\DigitalRiver\ApiSdk\Model\CheckoutTaxIdentifierRequest[]');
    $checkoutRequest->setBillTo($this->fillBilling($subscription->billing_info));
    // $checkoutRequest->setOrganization('\DigitalRiver\ApiSdk\Model\Organization');
    $checkoutRequest->setCurrency($subscription->currency);
    $checkoutRequest->setTaxInclusive(false);
    $checkoutRequest->setItems($this->fillCheckoutItems($subscription));
    $checkoutRequest->setChargeType(\DigitalRiver\ApiSdk\Model\ChargeType::CUSTOMER_INITIATED); // @phpstan-ignore-line
    $checkoutRequest->setCustomerType(\DigitalRiver\ApiSdk\Model\CustomerType::INDIVIDUAL); // @phpstan-ignore-line
    $checkoutRequest->setMetadata(['subscription_id' => $subscription->id]);
    $checkoutRequest->setUpstreamId((string)$subscription->id);

    try {
      return $this->checkoutApi->createCheckouts($checkoutRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function updateCheckoutTerms(string|int $checkoutId, string $terms): \DigitalRiver\ApiSdk\Model\Checkout|null
  {
    try {
      $checkout = $this->getCheckout((string)$checkoutId);

      $item1 = $checkout->getItems()[0];
      $item2 = $checkout->getItems()[1] ?? null;

      // subscription item
      $itemRequest = new \DigitalRiver\ApiSdk\Model\SkuUpdateRequestItem();
      $itemRequest->setId($item1->getId());
      $itemRequest->setSubscriptionInfo($item1->getSubscriptionInfo()->setTerms($terms));
      $items[] = $itemRequest;

      if ($item2) {
        $itemRequest = new \DigitalRiver\ApiSdk\Model\SkuUpdateRequestItem();
        $itemRequest->setId($item2->getId());
        $itemRequest->setSubscriptionInfo($item2->getSubscriptionInfo()->setTerms($terms));
        $items[] = $itemRequest;
      }
      $updateCheckoutRequest = new \DigitalRiver\ApiSdk\Model\UpdateCheckoutRequest();
      // $updateCheckoutRequest->setItems($items); // TODO: items or item?

      return $this->checkoutApi->updateCheckouts($checkoutId, $updateCheckoutRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function deleteCheckout(string|int $id): bool
  {
    try {
      $this->checkoutApi->deleteCheckouts((string)$id);
      return true;
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return false;
    }
  }

  public function attachCheckoutSource(string|int $id, string|int $sourceId): \DigitalRiver\ApiSdk\Model\Source|null
  {
    try {
      return $this->checkoutApi->attachSourceToCheckout((string)$id, (string)$sourceId);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  /**
   * order
   */
  public function getOrder(string|int $id): \DigitalRiver\ApiSdk\Model\Order|null
  {
    try {
      return $this->orderApi->retrieveOrders((string)$id);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function convertCheckoutToOrder(string|int $checkoutId): \DigitalRiver\ApiSdk\Model\Order|null
  {
    try {
      $orderRequest = new \DigitalRiver\ApiSdk\Model\OrderRequest();
      $orderRequest->setCheckoutId($checkoutId);
      return $this->orderApi->createOrders($orderRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function fulfillOrder(string|int $orderId, \DigitalRiver\ApiSdk\Model\Order $order = null): \DigitalRiver\ApiSdk\Model\Fulfillment|null
  {
    try {
      $order = $order ?? $this->getOrder($orderId);
      $orderItems = $order->getItems();

      $items = [];
      foreach ($orderItems as $orderItem) {

        $fulfillItem = new \DigitalRiver\ApiSdk\Model\FulfillmentRequestItem();
        $fulfillItem->setItemId($orderItem->getId());
        $fulfillItem->setQuantity($orderItem->getQuantity());
        $items[] = $fulfillItem;
      }
      $fulfillmentRequest = new \DigitalRiver\ApiSdk\Model\FulfillmentRequest();
      $fulfillmentRequest->setOrderId((string)$orderId);
      $fulfillmentRequest->setItems($items);

      return $this->fulfillmentApi->createFulfillments($fulfillmentRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  /**
   * invoice
   */
  // TODO:

  /**
   * subscription
   */
  public function getSubscription(string|int $id): \DigitalRiver\ApiSdk\Model\Subscription|null
  {
    try {
      return $this->subscriptionApi->retrieveSubscriptions($id);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function activateSubscription(string|int $id): \DigitalRiver\ApiSdk\Model\Subscription|null
  {
    $updateSubscriptionRequest = new  \DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest();
    $updateSubscriptionRequest->setState('active');

    try {
      return $this->subscriptionApi->updateSubscriptions((string)$id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function deleteSubscription(string|int $id): bool
  {
    try {
      $this->subscriptionApi->deleteSubscriptions($id);
      return true;
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return false;
    }
  }

  public function updateSubscriptionSource(string|int $id, string|int $sourceId)
  {
    $updateSubscriptionRequest = new  \DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest();
    $updateSubscriptionRequest->setSourceId($sourceId);

    try {
      return $this->subscriptionApi->updateSubscriptions((string)$id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  protected function fillSubscriptionSubscriptionItem(Subscription $subscription): \DigitalRiver\ApiSdk\Model\SubscriptionItems
  {
    // productDetails
    $productDetails = $this->fillSubscriptionItemProductDetails($subscription);

    // item
    $item = new \DigitalRiver\ApiSdk\Model\SubscriptionItems();
    $item->setProductDetails($productDetails);
    $item->setPrice($subscription->price);
    $item->setQuantity(1);

    return $item;
  }

  protected function fillSubscriptionProcessingFeeItem(Subscription $subscription): \DigitalRiver\ApiSdk\Model\SubscriptionItems
  {
    // productDetails
    $productDetails = $this->fillProcessingFeeItemProductDetails($subscription);

    // item
    $item = new \DigitalRiver\ApiSdk\Model\SubscriptionItems();
    $item->setProductDetails($productDetails);
    $item->setPrice($subscription->processing_fee);
    $item->setQuantity(1);

    return $item;
  }

  protected function fillSubscriptionItems(Subscription $subscription)
  {
    $items[] = $this->fillSubscriptionSubscriptionItem($subscription);
    if ($subscription->processing_fee_info['explicit_processing_fee']) {
      $items[] = $this->fillSubscriptionProcessingFeeItem($subscription);
    }
    return $items;
  }

  public function updateSubscriptionItems(string|int $id, Subscription $subscription)
  {
    // subscription.items[0]
    $items[] = $this->fillSubscriptionItems($subscription);

    $updateSubscriptionRequest = new  \DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest();
    $updateSubscriptionRequest->setItems($items);

    try {
      return $this->subscriptionApi->updateSubscriptions((string)$id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }

  public function cancelSubscription(string|int $id)
  {
    $updateSubscriptionRequest = new  \DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest();
    $updateSubscriptionRequest->setState('cancelled');

    try {
      return $this->subscriptionApi->updateSubscriptions((string)$id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning($th->getMessage());
      return null;
    }
  }


  /**
   * advanced function
   */
  public function paySubscription(string|int $id)
  {
  }



  /**
   * internal operation
   */
  protected function fulfillSubsciption()
  {
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
