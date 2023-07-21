<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Configuration;
use App\Models\Subscription;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use DigitalRiver\ApiSdk\Configuration as DrConfiguration;
use DigitalRiver\ApiSdk\Api\CheckoutsApi as DrCheckoutsApi;
use DigitalRiver\ApiSdk\Api\CustomersApi as DrCustomersApi;
use DigitalRiver\ApiSdk\Api\EventsApi as DrEventsApi;
use DigitalRiver\ApiSdk\Api\FileLinksApi as DrFileLinksApi;
use DigitalRiver\ApiSdk\Api\FulfillmentsApi as DrFulfillmentsApi;
use DigitalRiver\ApiSdk\Api\OrdersApi as DrOrdersApi;
use DigitalRiver\ApiSdk\Api\PlansApi as DrPlansApi;
use DigitalRiver\ApiSdk\Api\SourcesApi as DrSourcesApi;
use DigitalRiver\ApiSdk\Api\SubscriptionsApi as DrSubscriptionsApi;
use DigitalRiver\ApiSdk\Api\WebhooksApi as DrWebhooksApi;
use DigitalRiver\ApiSdk\Model\Address as DrAddress;
use DigitalRiver\ApiSdk\Model\Billing as DrBilling;
use DigitalRiver\ApiSdk\Model\ChargeType as DrChargeType;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\CustomerRequest as DrCustomerRequest;
use DigitalRiver\ApiSdk\Model\CustomerType as DrCustomerType;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\FileLinkRequest as DrFileLinkRequest;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\FulfillmentRequest as DrFulfillmentRequest;
use DigitalRiver\ApiSdk\Model\FulfillmentRequestItem as DrFulfillmentRequestItem;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRequest as DrOrderRequest;
use DigitalRiver\ApiSdk\Model\Plan as DrPlan;
use DigitalRiver\ApiSdk\Model\PlanRequest as DrPlanRequest;
use DigitalRiver\ApiSdk\Model\ProductDetails as DrProductDetails;
use DigitalRiver\ApiSdk\Model\Shipping as DrShipping;
use DigitalRiver\ApiSdk\Model\SkuDiscount as DrSkuDiscount;
use DigitalRiver\ApiSdk\Model\SkuRequestItem as DrSkuRequestItem;
use DigitalRiver\ApiSdk\Model\SkuUpdateRequestItem as DrSkuUpdateRequestItem;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\SubscriptionInfo as DrSubscriptionInfo;
use DigitalRiver\ApiSdk\Model\SubscriptionItems as DrSubscriptionItems;
use DigitalRiver\ApiSdk\Model\UpdateCheckoutRequest as DrUpdateCheckoutRequest;
use DigitalRiver\ApiSdk\Model\UpdateCustomerRequest as DrUpdateCustomerRequest;
use DigitalRiver\ApiSdk\Model\UpdatePlanRequest as DrUpdatePlanRequest;
use DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest as DrUpdateSubscriptionRequest;
use DigitalRiver\ApiSdk\Model\WebhookUpdateRequest as DrWebhookUpdateRequest;


class DigitalRiverService
{
  /** @var Client $client */
  public $client = null;

  /** @var DrConfiguration DR configuration */
  public $config = null;

  /** @var DrPlansApi|null */
  public $planApi = null;

  /** @var DrCustomersApi|null */
  public $customerApi = null;

  /** @var DrCheckoutsApi|null */
  public $checkoutApi = null;

  /** @var DrSubscriptionsApi|null */
  public $subscriptionApi = null;

  /** @var DrOrdersApi|null */
  public $orderApi = null;

  /** @var DrFulfillmentsApi|null */
  public $fulfillmentApi = null;

  /** @var DrSourcesApi|null */
  public $sourceApi = null;

  /** @var DrEventsApi|null */
  public $eventApi = null;

  /** @var DrWebhooksApi|null */
  public $webhookApi = null;

  /** @var DrFileLinksApi|null */
  public $fileLinkApi = null;

  public function __construct()
  {
    // rest api client
    $this->client = new Client();

    // DR configuration
    $this->config = DrConfiguration::getDefaultConfiguration();
    $this->config->setAccessToken(config('dr.token'));
    $this->config->setHost(config('dr.host'));

    // DR apis
    $this->planApi          = new DrPlansApi($this->client, $this->config);
    $this->checkoutApi      = new DrCheckoutsApi($this->client, $this->config);
    $this->subscriptionApi  = new DrSubscriptionsApi($this->client, $this->config);
    $this->orderApi         = new DrOrdersApi($this->client, $this->config);
    $this->fulfillmentApi   = new DrFulfillmentsApi($this->client, $this->config);
    $this->customerApi      = new DrCustomersApi($this->client, $this->config);
    $this->sourceApi        = new DrSourcesApi($this->client, $this->config);
    $this->eventApi         = new DrEventsApi($this->client, $this->config);
    $this->webhookApi       = new DrWebhooksApi($this->client, $this->config);
    $this->fileLinkApi      = new DrfileLinksApi($this->client, $this->config);
  }

  /**
   * helper function
   */
  protected function fillAddress(array $addr): DrAddress
  {
    $address = new DrAddress();
    $address->setLine1($addr['line1']);
    $address->setLine2($addr['line2']);
    $address->setCity($addr['city']);
    $address->setPostalCode($addr['postcode']);
    $address->setState($addr['state']);
    $address->setCountry($addr['country']);
    return $address;
  }

  protected function fillShipping(BillingInfo|array $billingInfo): DrShipping
  {
    $shipping = new DrShipping();
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

  protected function fillBilling(BillingInfo|array $billingInfo): DrBilling
  {
    $billTo = new DrBilling();
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

  protected function fillSubscriptionItemProductDetails(Subscription $subscription): DrProductDetails
  {
    $productDetails = new DrProductDetails();
    $productDetails->setSkuGroupId(config('dr.sku_grp_subscription'));
    $productName = $subscription->plan_info['name'] . ($subscription->coupon_info ? '(' . $subscription->coupon_info['code'] . ')' : '');
    $productDetails->setName($productName);
    $productDetails->setDescription("");
    $productDetails->setCountryOfOrigin('AU');

    return $productDetails;
  }

  /**
   * plan
   */
  public function getDefaultPlan(): DrPlan
  {
    try {
      return $this->planApi->retrievePlans(config('dr.default_plan'));
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function createDefaultPlan(Configuration $configuration): DrPlan
  {
    $planRequest = new DrPlanRequest();

    $planRequest->setId(config('dr.default_plan'));
    $planRequest->setTerms('These are the terms...');
    $planRequest->setContractBindingDays(10000);
    if (config('dr.dr_mode') != 'prod') {
      $planRequest->setName(config('dr.dr_test.name'));
      $planRequest->setInterval('day');
      $planRequest->setIntervalCount(config('dr.dr_test.interval_count'));
    } else {
      $planRequest->setName('default-plan');
      $planRequest->setInterval('day');
      $planRequest->setInterval('month');
      $planRequest->setIntervalCount(1);
    }
    $planRequest->setBillingOffsetDays($configuration->plan_billing_offset_days);
    $planRequest->setReminderOffsetDays($configuration->plan_reminder_offset_days);
    $planRequest->setCollectionPeriodDays($configuration->plan_collection_period_days);

    $planRequest->setState('active');

    try {
      return $this->planApi->createPlans($planRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }


  public function UpdateDefaultPlan(Configuration $configuration): DrPlan
  {
    $planRequest = new DrUpdatePlanRequest();
    $planRequest->setReminderOffsetDays($configuration->plan_reminder_offset_days);
    $planRequest->setBillingOffsetDays($configuration->plan_billing_offset_days);
    $planRequest->setCollectionPeriodDays($configuration->plan_collection_period_days);

    try {
      return $this->planApi->updatePlans(config('dr.default_plan'), $planRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function updateDefaultWebhook(array $types, bool $enable)
  {
    try {
      $webhookUpdateRequest = new DrWebhookUpdateRequest();
      $webhookUpdateRequest->setTypes($types);
      $webhookUpdateRequest->setEnabled($enable);
      return $this->webhookApi->updateWebhooks(config('dr.default_webhook'), $webhookUpdateRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * customer
   */
  public function getCustomer(string $id): DrCustomer
  {
    try {
      return $this->customerApi->retrieveCustomers($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function createCustomer(BillingInfo $billingInfo): DrCustomer
  {
    $customerRequest = new DrCustomerRequest();
    // $customerRequest->setId('customer-' . $billingInfo->user_id);
    $customerRequest->setEmail($billingInfo->email);
    $customerRequest->setShipping($this->fillShipping($billingInfo));
    $customerRequest->setMetadata(['user_id' => $billingInfo->user_id]);

    try {
      return $this->customerApi->createCustomers($customerRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function updateCustomer(string $id, BillingInfo $billingInfo): DrCustomer
  {
    $customerRequest = new DrUpdateCustomerRequest();
    $customerRequest->setEmail($billingInfo->email);
    $customerRequest->setShipping($this->fillShipping($billingInfo));

    try {
      return $this->customerApi->updateCustomers($id, $customerRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function attachCustomerSource(string $customerId, string $source_id): DrSource
  {
    try {
      return $this->customerApi->createCustomerSource($customerId, $source_id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function detachCustomerSource(string $customerId, string $source_id): bool
  {
    try {
      $this->customerApi->deleteCustomerSource($customerId, $source_id);
      return true;
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      return false;
    }
  }

  public function detachCustomerSourceAsync(string $customerId, string $source_id)
  {
    return $this->customerApi->deleteCustomerSourceAsync($customerId, $source_id);
  }

  /**
   * checkout
   */
  public function getCheckout(string $id): DrCheckout
  {
    try {
      return $this->checkoutApi->retrieveCheckouts($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }


  protected function fillCheckoutSubscriptionItem(Subscription $subscription): DrSkuRequestItem
  {
    // productDetails
    $productDetails = $this->fillSubscriptionItemProductDetails($subscription);

    // subscriptionInfo
    $subscriptionInfo = new DrSubscriptionInfo();
    $subscriptionInfo->setPlanId(config('dr.default_plan'));
    $subscriptionInfo->setTerms('These are the terms...');
    $subscriptionInfo->setAutoRenewal(true);

    // discount
    $discount = null;
    if ($subscription->coupon_info) {
      $discount = new DrSkuDiscount();
      $discount->setPercentOff($subscription->coupon_info['percentage_off']);
    }

    // item
    $item = new DrSkuRequestItem();
    $item->setProductDetails($productDetails);
    $item->setSubscriptionInfo($subscriptionInfo);
    $item->setPrice($subscription->price);
    $item->setQuantity(1);
    $item->setMetadata(['subscription' => $subscription->id]);
    if ($discount) {
      $item->setDiscount($discount);
    }

    return $item;
  }

  protected function fillCheckoutItems(Subscription $subscription)
  {
    $items[] = $this->fillCheckoutSubscriptionItem($subscription);
    return $items;
  }

  public function createCheckout(Subscription $subscription): DrCheckout
  {
    // checkout
    $checkoutRequest = new DrCheckoutRequest();
    $checkoutRequest->setCustomerId((string)$subscription->user->dr['customer_id']);
    $checkoutRequest->setEmail($subscription->billing_info['email']);
    $checkoutRequest->setLocale($subscription->billing_info['locale']);
    $checkoutRequest->setBrowserIp(request()->ip());
    // $checkoutRequest->setTaxIdentifiers('DrCheckoutTaxIdentifierRequest[]');
    $checkoutRequest->setBillTo($this->fillBilling($subscription->billing_info));
    // $checkoutRequest->setOrganization('DrOrganization');
    $checkoutRequest->setCurrency($subscription->currency);
    $checkoutRequest->setTaxInclusive(false);
    $checkoutRequest->setItems($this->fillCheckoutItems($subscription));
    $checkoutRequest->setChargeType(DrChargeType::CUSTOMER_INITIATED); // @phpstan-ignore-line
    $checkoutRequest->setCustomerType(DrCustomerType::INDIVIDUAL); // @phpstan-ignore-line
    $checkoutRequest->setMetadata(['subscription_id' => $subscription->id]);
    $checkoutRequest->setUpstreamId((string)$subscription->id);

    try {
      return $this->checkoutApi->createCheckouts($checkoutRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function updateCheckoutTerms(string $checkoutId, string $terms): DrCheckout
  {
    try {
      $checkout = $this->getCheckout($checkoutId);


      $items = [];
      foreach ($checkout->getItems() as $checkoutItem) {
        // subscription item
        $itemRequest = new DrSkuUpdateRequestItem();
        $itemRequest->setId($checkoutItem->getId());
        $itemRequest->setSubscriptionInfo($checkoutItem->getSubscriptionInfo()->setTerms($terms));
        $items[] = $itemRequest;
      }
      $updateCheckoutRequest = new DrUpdateCheckoutRequest();
      $updateCheckoutRequest->setItems($items);
      $updateCheckoutRequest->setBrowserIp(request()->ip());

      return $this->checkoutApi->updateCheckouts($checkoutId, $updateCheckoutRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function deleteCheckout(string $id): bool
  {
    try {
      $this->checkoutApi->deleteCheckouts($id);
      return true;
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      return false;
    }
  }

  public function deleteCheckoutAsync(string $id)
  {
    return $this->checkoutApi->deleteCheckoutsAsync($id);
  }

  public function attachCheckoutSource(string $id, string $sourceId): DrSource
  {
    try {
      return $this->checkoutApi->attachSourceToCheckout($id, $sourceId);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * source
   */
  public function getSource(string $id): DrSource
  {
    try {
      return $this->sourceApi->retrieveSources($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * order
   */
  public function getOrder(string $id): DrOrder
  {
    try {
      return $this->orderApi->retrieveOrders($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function convertCheckoutToOrder(string $checkoutId): DrOrder
  {
    try {
      $orderRequest = new DrOrderRequest();
      $orderRequest->setCheckoutId($checkoutId);
      $orderRequest->setBrowserIp(request()->ip());
      return $this->orderApi->createOrders($orderRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function fulfillOrder(string $orderId, DrOrder $order = null, bool $cancel = false): DrFulfillment
  {
    try {
      $order = $order ?? $this->getOrder($orderId);
      $orderItems = $order->getItems();

      $items = [];
      foreach ($orderItems as $orderItem) {

        $fulfillItem = new DrFulfillmentRequestItem();
        $fulfillItem->setItemId($orderItem->getId());
        if ($cancel) {
          $fulfillItem->setCancelQuantity($orderItem->getQuantity());
        } else {
          $fulfillItem->setQuantity($orderItem->getQuantity());
        }
        $items[] = $fulfillItem;
      }
      $fulfillmentRequest = new DrFulfillmentRequest();
      $fulfillmentRequest->setOrderId((string)$order->getId());
      $fulfillmentRequest->setItems($items);

      return $this->fulfillmentApi->createFulfillments($fulfillmentRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * invoice
   */
  // TODO:

  /**
   * subscription
   */
  public function getSubscription(string $id): DrSubscription
  {
    try {
      return $this->subscriptionApi->retrieveSubscriptions($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function activateSubscription(string $id): DrSubscription
  {
    $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
    $updateSubscriptionRequest->setState('active');

    try {
      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function deleteSubscription(string $id): bool
  {
    try {
      $this->subscriptionApi->deleteSubscriptions($id);
      return true;
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      return false;
    }
  }

  public function deleteSubscriptionAsync(string $id)
  {
    return $this->subscriptionApi->deleteSubscriptionsAsync($id);
  }

  public function updateSubscriptionSource(string $id, string $sourceId)
  {
    $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
    $updateSubscriptionRequest->setSourceId($sourceId);

    try {
      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  protected function fillSubscriptionSubscriptionItem(Subscription $subscription): DrSubscriptionItems
  {
    // productDetails
    $productDetails = $this->fillSubscriptionItemProductDetails($subscription);

    // item
    $item = new DrSubscriptionItems();
    $item->setProductDetails($productDetails);
    $item->setPrice($subscription->price);
    $item->setQuantity(1);

    return $item;
  }

  protected function fillSubscriptionItems(Subscription $subscription)
  {
    $items[] = $this->fillSubscriptionSubscriptionItem($subscription);
    return $items;
  }

  public function updateSubscriptionItems(string $id, Subscription $subscription)
  {
    // subscription.items[0] TODO: fillNextInvoice items
    $items[] = $this->fillSubscriptionItems($subscription);

    $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
    $updateSubscriptionRequest->setItems($items);

    try {
      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function cancelSubscription(string $id)
  {
    $updateSubscriptionRequest = new DrUpdateSubscriptionRequest();
    $updateSubscriptionRequest->setState('cancelled');

    try {
      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * webhook
   */
  public function listEvents()
  {
    try {
      $events = $this->eventApi->listEvents();
      return $events->getData();
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  public function createFileLink(string $fileId, Carbon $expiresTime): DrFileLink
  {
    $fileLinkRequest = new DrFileLinkRequest();
    $fileLinkRequest->setFileId($fileId);
    $fileLinkRequest->setExpiresTime($expiresTime->toIso8601ZuluString()); // @phpstan-ignore-line

    try {
      return $this->fileLinkApi->createFileLinks($fileLinkRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }
}
