<?php

namespace App\Services\DigitalRiver;

use App\Models\BillingInfo;
use App\Models\Configuration;
use App\Models\Coupon;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use DigitalRiver\ApiSdk\ApiException as DrApiException;
use DigitalRiver\ApiSdk\Configuration as DrConfiguration;
use DigitalRiver\ApiSdk\Api\CheckoutsApi as DrCheckoutsApi;
use DigitalRiver\ApiSdk\Api\CustomersApi as DrCustomersApi;
use DigitalRiver\ApiSdk\Api\EventsApi as DrEventsApi;
use DigitalRiver\ApiSdk\Api\FileLinksApi as DrFileLinksApi;
use DigitalRiver\ApiSdk\Api\FulfillmentsApi as DrFulfillmentsApi;
use DigitalRiver\ApiSdk\Api\InvoicesApi as DrInvoicesApi;
use DigitalRiver\ApiSdk\Api\OrdersApi as DrOrdersApi;
use DigitalRiver\ApiSdk\Api\PlansApi as DrPlansApi;
use DigitalRiver\ApiSdk\Api\RefundsApi as DrRefundsApi;
use DigitalRiver\ApiSdk\Api\SourcesApi as DrSourcesApi;
use DigitalRiver\ApiSdk\Api\SubscriptionsApi as DrSubscriptionsApi;
use DigitalRiver\ApiSdk\Api\TaxIdentifiersApi as DrTaxIdentifiersApi;
use DigitalRiver\ApiSdk\Api\WebhooksApi as DrWebhooksApi;
use DigitalRiver\ApiSdk\Model\Address as DrAddress;
use DigitalRiver\ApiSdk\Model\Billing as DrBilling;
use DigitalRiver\ApiSdk\Model\ChargeType as DrChargeType;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\CheckoutTaxIdentifierRequest as DrCheckoutTaxIdentifierRequest;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\CustomerRequest as DrCustomerRequest;
use DigitalRiver\ApiSdk\Model\CustomerTaxIdentifier as DrCustomerTaxIdentifier;
use DigitalRiver\ApiSdk\Model\CustomerType as DrCustomerType;
use DigitalRiver\ApiSdk\Model\Event as DrEvent;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\FileLinkRequest as DrFileLinkRequest;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\FulfillmentRequest as DrFulfillmentRequest;
use DigitalRiver\ApiSdk\Model\FulfillmentRequestItem as DrFulfillmentRequestItem;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\OrderRequest as DrOrderRequest;
use DigitalRiver\ApiSdk\Model\Plan as DrPlan;
use DigitalRiver\ApiSdk\Model\PlanRequest as DrPlanRequest;
use DigitalRiver\ApiSdk\Model\ProductDetails as DrProductDetails;
use DigitalRiver\ApiSdk\Model\RefundRequest as DrRefundRequest;
use DigitalRiver\ApiSdk\Model\Shipping as DrShipping;
use DigitalRiver\ApiSdk\Model\SkuDiscount as DrSkuDiscount;
use DigitalRiver\ApiSdk\Model\SkuRequestItem as DrSkuRequestItem;
use DigitalRiver\ApiSdk\Model\SkuUpdateRequestItem as DrSkuUpdateRequestItem;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\SubscriptionInfo as DrSubscriptionInfo;
use DigitalRiver\ApiSdk\Model\SubscriptionItems as DrSubscriptionItems;
use DigitalRiver\ApiSdk\Model\TaxIdentifier as DrTaxIdentifier;
use DigitalRiver\ApiSdk\Model\TaxIdentifierRequest as DrTaxIdentifierRequest;
use DigitalRiver\ApiSdk\Model\UpdateCheckoutRequest as DrUpdateCheckoutRequest;
use DigitalRiver\ApiSdk\Model\UpdateCustomerRequest as DrUpdateCustomerRequest;
use DigitalRiver\ApiSdk\Model\UpdateOrderRequest;
use DigitalRiver\ApiSdk\Model\UpdatePlanRequest as DrUpdatePlanRequest;
use DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest as DrUpdateSubscriptionRequest;
use DigitalRiver\ApiSdk\Model\WebhookUpdateRequest as DrWebhookUpdateRequest;
use Exception;


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

  /** @var DrTaxIdentifiersApi|null */
  public $taxIdentifierApi = null;

  /** @var DrOrdersApi|null */
  public $orderApi = null;

  /** @var DrInvoicesApi|null */
  public $invoiceApi = null;

  /** @var DrFulfillmentsApi|null */
  public $fulfillmentApi = null;

  /** @var DrSourcesApi|null */
  public $sourceApi = null;

  /** @var DrRefundsApi|null */
  public $refundApi = null;

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
    $this->taxIdentifierApi = new DrTaxIdentifiersApi($this->client, $this->config);
    $this->orderApi         = new DrOrdersApi($this->client, $this->config);
    $this->invoiceApi       = new DrInvoicesApi($this->client, $this->config);
    $this->refundApi        = new DrRefundsApi($this->client, $this->config);
    $this->fulfillmentApi   = new DrFulfillmentsApi($this->client, $this->config);
    $this->customerApi      = new DrCustomersApi($this->client, $this->config);
    $this->sourceApi        = new DrSourcesApi($this->client, $this->config);
    $this->eventApi         = new DrEventsApi($this->client, $this->config);
    $this->webhookApi       = new DrWebhooksApi($this->client, $this->config);
    $this->fileLinkApi      = new DrfileLinksApi($this->client, $this->config);
  }

  protected function throwException(\Throwable $th, string $level = 'error'): Exception
  {
    if ($th instanceof DrApiException) {
      if ($th->getResponseObject()) {
        $message = $th->getResponseObject()->getErrors()[0]?->getMessage() ?? 'Unknown error';
      } else {
        $text = $th->getResponseBody() ?? $th->getMessage();
        $body = json_decode($text);
        $message = $body->errors[0]->message ?? $text;
      }
    } else {
      $text = $th->getMessage();
      $body = json_decode($text);
      $message = $body->errors[0]->message ?? $text;
    }
    Log::log($level, $th);
    return new Exception("{$message}", ($th->getCode() >= 100 && $th->getCode() < 600) ? $th->getCode() : 500);
  }

  /**
   * create and fill a DrAddress object
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

  /**
   * create and fill a DrShipping object from a BillingInfo object
   */
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

  /**
   * create and fill a DrBilling object from a BillingInfo object
   */
  protected function fillBilling(BillingInfo|array $billingInfo): DrBilling
  {
    $billTo = new DrBilling();
    if ($billingInfo instanceof BillingInfo) {
      $billTo->setName($billingInfo->first_name . ' ' . $billingInfo->last_name);
      $billTo->setPhone($billingInfo->phone);
      $billTo->setEmail($billingInfo->email);
      $billTo->setOrganization($billingInfo->organization ?: null);
      $billTo->setAddress($this->fillAddress($billingInfo->address));
    } else {
      $billTo->setName($billingInfo['first_name'] . ' ' . $billingInfo['last_name']);
      $billTo->setPhone($billingInfo['phone']);
      $billTo->setEmail($billingInfo['email']);
      $billTo->setOrganization($billingInfo['organization'] ?: null);
      $billTo->setAddress($this->fillAddress($billingInfo['address']));
    }
    return $billTo;
  }

  /**
   * list DrPlan
   *
   * @return DrPlan[]
   */
  public function listPlan(): array
  {
    try {
      return $this->planApi->listPlans(state: 'active', limit: 100)->getData() ?? [];
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get DrPlan
   */
  public function getPlan(string $drPlanId): DrPlan
  {
    try {
      return $this->planApi->retrievePlans($drPlanId);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * create DrPlan
   */
  public function createPlan(SubscriptionPlan $myPlan): DrPlan
  {
    try {
      $planRequest = new DrPlanRequest();

      $planRequest->setTerms('These are the terms...');
      $planRequest->setContractBindingDays($myPlan->contract_binding_days);
      $planRequest->setName($myPlan->name);
      $planRequest->setInterval($myPlan->interval);
      $planRequest->setIntervalCount($myPlan->interval_count);
      $planRequest->setBillingOffsetDays($myPlan->billing_offset_days);
      $planRequest->setReminderOffsetDays($myPlan->reminder_offset_days);
      $planRequest->setCollectionPeriodDays($myPlan->collection_period_days);
      $planRequest->setState($myPlan->status);

      return $this->planApi->createPlans($planRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * update DrPlan
   */
  public function updatePlan(SubscriptionPlan $subscriptionPlan): DrPlan
  {
    try {
      $planRequest = new DrUpdatePlanRequest();
      $planRequest->setBillingOffsetDays($subscriptionPlan->billing_offset_days);
      $planRequest->setReminderOffsetDays($subscriptionPlan->reminder_offset_days);
      $planRequest->setCollectionPeriodDays($subscriptionPlan->collection_period_days);
      $planRequest->setState($subscriptionPlan->status);

      return $this->planApi->updatePlans($subscriptionPlan->dr_plan_id, $planRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * update & enable the default DrWebhook
   */
  public function updateDefaultWebhook(array $types, bool $enable)
  {
    try {
      $webhookUpdateRequest = new DrWebhookUpdateRequest();
      $webhookUpdateRequest->setTypes($types);
      $webhookUpdateRequest->setEnabled($enable);
      return $this->webhookApi->updateWebhooks(config('dr.default_webhook'), $webhookUpdateRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get DrCustomer by dr_customer_id
   */
  public function getCustomer(string $id): DrCustomer
  {
    try {
      return $this->customerApi->retrieveCustomers($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * create a DrCustomer from billing info
   */
  public function createCustomer(BillingInfo $billingInfo): DrCustomer
  {
    try {
      $customerRequest = new DrCustomerRequest();
      $customerRequest->setType($billingInfo->customer_type); // @phpstan-ignore-line
      $customerRequest->setEmail($billingInfo->email);
      $customerRequest->setShipping($this->fillShipping($billingInfo));
      $customerRequest->setMetadata(['user_id' => $billingInfo->user_id]);

      return $this->customerApi->createCustomers($customerRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * update a DrCustomer from billing info
   * @param string $id dr customer id
   */
  public function updateCustomer(string $id, BillingInfo $billingInfo): DrCustomer
  {
    try {
      $customerRequest = new DrUpdateCustomerRequest();
      $customerRequest->setType($billingInfo->customer_type); // @phpstan-ignore-line
      $customerRequest->setEmail($billingInfo->email);
      $customerRequest->setShipping($this->fillShipping($billingInfo));

      return $this->customerApi->updateCustomers($id, $customerRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * attach source to DrCustomer
   * @param string $customerId dr customer id
   * @param string $source_id dr source id
   */
  public function attachCustomerSource(string $customerId, string $source_id): DrSource
  {
    try {
      return $this->customerApi->createCustomerSource($customerId, $source_id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * detach source from DrCustomer
   * @param string $customerId dr customer id
   * @param string $source_id dr source id
   */
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

  /**
   * get DrCheckout by dr checkout id
   * @param string $id dr checkout id
   */
  public function getCheckout(string $id): DrCheckout
  {
    try {
      return $this->checkoutApi->retrieveCheckouts($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * create & fill a DrSkuRequestItem array from a subscription
   *
   * @return DrSkuRequestItem[]
   */
  protected function fillCheckoutItems(Subscription $subscription): array
  {
    // free trial
    if ($subscription->isFreeTrial()) {
      $drPlanId = SubscriptionPlan::findFreePlanDrId(
        $subscription->coupon_info['interval'],
        $subscription->coupon_info['interval_count']
      );
    } else {
      $drPlanId = SubscriptionPlan::findNormalPlanDrId(
        $subscription->plan_info['interval'],
        $subscription->plan_info['interval_count']
      );
    }

    // subscriptionInfo
    $subscriptionInfo = new DrSubscriptionInfo();
    $subscriptionInfo->setPlanId($drPlanId);
    $subscriptionInfo->setTerms('These are the terms...');
    $subscriptionInfo->setAutoRenewal(true);
    $subscriptionInfo->setFreeTrial($subscription->isFreeTrial());

    $items = [];

    foreach ($subscription->items as $productItem) {

      $productDetails = new DrProductDetails();
      $productDetails->setSkuGroupId(config('dr.sku_grp_subscription'));
      $productDetails->setName($productItem['name']);
      $productDetails->setDescription("");
      $productDetails->setCountryOfOrigin('AU');

      // item
      $item = new DrSkuRequestItem();
      $item->setProductDetails($productDetails);
      $item->setSubscriptionInfo($subscriptionInfo);
      $item->setPrice($productItem['price']);
      $item->setQuantity(1);
      $item->setMetadata([
        'subscription_id' => $subscription->id,
        'category' => $productItem['category']
      ]);

      $items[] = $item;
    }
    return $items;
  }

  /**
   * create a DrCheckout from a subscription
   */
  public function createCheckout(Subscription $subscription): DrCheckout
  {
    try {
      // checkout
      $checkoutRequest = new DrCheckoutRequest();
      $checkoutRequest->setCustomerId((string)$subscription->user->getDrCustomerId());
      $checkoutRequest->setCustomerType($subscription->billing_info['customer_type']);
      $checkoutRequest->setEmail($subscription->billing_info['email']);
      $checkoutRequest->setLocale($subscription->billing_info['locale']);
      $checkoutRequest->setBrowserIp(request()->ip());
      $checkoutRequest->setBillTo($this->fillBilling($subscription->billing_info));
      $checkoutRequest->setCurrency($subscription->currency);
      $checkoutRequest->setTaxInclusive(false);
      $checkoutRequest->setItems($this->fillCheckoutItems($subscription));
      $checkoutRequest->setChargeType(DrChargeType::CUSTOMER_INITIATED); // @phpstan-ignore-line
      $checkoutRequest->setMetadata(['subscription_id' => $subscription->id]);
      $checkoutRequest->setUpstreamId((string)$subscription->active_invoice_id);

      // set tax id
      if (!empty($subscription->tax_id_info)) {
        $checkoutRequest->setTaxIdentifiers([(new DrCheckoutTaxIdentifierRequest())->setId($subscription->tax_id_info['dr_tax_id'])]);
      } else {
        $checkoutRequest->setTaxIdentifiers([]);
      }

      return $this->checkoutApi->createCheckouts($checkoutRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * retrieve tax rate
   */

  public function retrieveTaxRate(User $user, TaxId $taxId = null): float
  {
    $billing_info = $user->billing_info->info();

    try {
      // tax retrieve checkout
      $checkoutRequest = new DrCheckoutRequest();
      $checkoutRequest->setCustomerId($user->getDrCustomerId());
      $checkoutRequest->setCurrency('USD');
      $checkoutRequest->setEmail($billing_info['email']);
      $checkoutRequest->setBrowserIp(request()->ip());
      $checkoutRequest->setBillTo((new DrBilling())
        ->setEmail($billing_info['email'])
        ->setAddress((new DrAddress())
          ->setLine1($billing_info['address']['line1'])
          ->setLine2($billing_info['address']['line2'])
          ->setCity($billing_info['address']['city'])
          ->setPostalCode($billing_info['address']['postcode'])
          ->setState($billing_info['address']['state'])
          ->setCountry($billing_info['address']['country'])));
      $checkoutRequest->setItems([
        (new DrSkuRequestItem())
          ->setProductDetails((new DrProductDetails())
            ->setSkuGroupId(config('dr.sku_grp_subscription'))
            ->setName('Tax Rate Precalculation'))
          ->setPrice(10.00)
      ]);
      $checkoutRequest->setTaxInclusive(false);
      $checkoutRequest->setCustomerType($billing_info['customer_type']);
      $checkoutRequest->setTaxIdentifiers($taxId ? [(new DrCheckoutTaxIdentifierRequest())->setId($taxId->dr_tax_id)] : []);
      $checkoutRequest->setUpstreamId(config('dr.tax_rate_pre_calcualte_id'));

      // retrieve tax rate
      $checkout = $this->checkoutApi->createCheckouts($checkoutRequest);
      $taxRate = ($checkout->getItems()[0]->getTax()->getAmount() == 0) ? 0 : $checkout->getItems()[0]->getTax()->getRate();

      // remove checkout (TODO: moved to after response?)
      $this->checkoutApi->deleteCheckouts($checkout->getId());

      if ($taxRate === null) {
        throw new Exception('Can not retrieve tax rate, please check your billing information.', 400);
      }
      return $taxRate;
    } catch (\Throwable $th) {
      throw $this->throwException($th, 'warning');
    }
  }

  public function updateCheckout(string $checkoutId, string $terms = null, string $taxId = null): DrCheckout
  {
    try {
      $checkout = $this->getCheckout($checkoutId);

      // update checkout terms
      $updateCheckoutRequest = new DrUpdateCheckoutRequest();
      if ($terms) {
        $items = [];
        foreach ($checkout->getItems() as $checkoutItem) {
          // subscription item
          $itemRequest = new DrSkuUpdateRequestItem();
          $itemRequest->setId($checkoutItem->getId());
          $itemRequest->setSubscriptionInfo($checkoutItem->getSubscriptionInfo()->setTerms($terms));
          $items[] = $itemRequest;
        }
        $updateCheckoutRequest->setItems($items);
      }
      if ($taxId) {
        $updateCheckoutRequest->setTaxIdentifiers([(new DrCheckoutTaxIdentifierRequest())->setId($taxId)]);
      }
      $updateCheckoutRequest->setBrowserIp(request()->ip());

      return $this->checkoutApi->updateCheckouts($checkoutId, $updateCheckoutRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function updateCheckoutTerms(string $checkoutId, string $terms): DrCheckout
  {
    return $this->updateCheckout($checkoutId, terms: $terms);
  }

  public function updateCheckoutTaxId(string $checkoutId, string $taxId): DrCheckout
  {
    return $this->updateCheckout($checkoutId, taxId: $taxId);
  }

  /**
   * delete a DrCheckout
   * @param string $id dr checkout id
   */
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

  /**
   * attach a dr source to a dr checkout
   * @param string $id dr checkout id
   * @param string $sourceId dr source id
   */
  public function attachCheckoutSource(string $id, string $sourceId): DrSource
  {
    try {
      return $this->checkoutApi->attachSourceToCheckout($id, $sourceId);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get a DrSource by id
   * @param string $id dr source id
   */
  public function getSource(string $id): DrSource
  {
    try {
      return $this->sourceApi->retrieveSources($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get a DrOrder by id
   * @param string $id dr order id
   */
  public function getOrder(string $id): DrOrder
  {
    try {
      return $this->orderApi->retrieveOrders($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function updateOrderUpstreamId(string $id, string|int $upstreamId): DrOrder
  {
    try {
      $orderUpdateRequest = new UpdateOrderRequest();
      $orderUpdateRequest->setUpstreamId((string)$upstreamId);
      return  $this->orderApi->updateOrders($id, $orderUpdateRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * convert a DrCheckout to a DrOrder
   * @param string $checkoutId dr checkout id
   */
  public function convertCheckoutToOrder(string $checkoutId): DrOrder
  {
    try {
      $orderRequest = new DrOrderRequest();
      $orderRequest->setCheckoutId($checkoutId);
      $orderRequest->setBrowserIp(request()->ip());
      return $this->orderApi->createOrders($orderRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th, 'warning');
    }
  }

  /**
   * fulfill a DrOrder
   * @param string $orderId dr order id
   * @param DrOrder $order dr order
   * @param bool $cancel fulfill or cancel
   */
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
      throw $this->throwException($th);
    }
  }

  /**
   * get a DrInvoice by id
   * @param string $id dr invoice id
   */
  public function getInvoice(string $id): DrInvoice
  {
    try {
      return $this->invoiceApi->retrieveInvoices($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }


  /**
   * get a DrSubscription by id
   * @param string $id dr subscription id
   */
  public function getSubscription(string $id): DrSubscription
  {
    try {
      return $this->subscriptionApi->retrieveSubscriptions($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * activate a DrSubscription
   * @param string $id dr subscription id
   */
  public function activateSubscription(string $id): DrSubscription
  {
    try {
      $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
      $updateSubscriptionRequest->setState('active');

      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function convertSubscriptionToStandard(DrSubscription $drSubscription, Subscription $subscription): DrSubscription
  {
    try {
      $items = $drSubscription->getItems();
      $items[0]->setPrice($subscription->plan_info['price']['price']);
      $items[0]->getProductDetails()->setName($subscription->plan_info['name']);

      $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
      $updateSubscriptionRequest->setPlanId(
        SubscriptionPlan::findNormalPlanDrId(
          $subscription->plan_info['interval'],
          $subscription->plan_info['interval_count']
        )
      );
      $updateSubscriptionRequest->setItems($items);

      return $this->subscriptionApi->updateSubscriptions($drSubscription->getId(), $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function convertSubscriptionToNext(DrSubscription $drSubscription, Subscription $subscription): DrSubscription
  {
    try {
      $nextInvoice = $subscription->next_invoice;

      $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();

      // update dr plan if requied
      $newDrPlanId = SubscriptionPlan::findNormalPlanDrId(
        $nextInvoice['plan_info']['interval'],
        $nextInvoice['plan_info']['interval_count']
      );
      if ($newDrPlanId != $drSubscription->getPlanId()) {
        $updateSubscriptionRequest->setPlanId($newDrPlanId);
      }

      // update items
      $items = $drSubscription->getItems();
      $items[0]->setPrice($nextInvoice['price']);
      $items[0]->getProductDetails()->setName(Subscription::buildPlanName($nextInvoice['plan_info'], $nextInvoice['coupon_info']));
      $updateSubscriptionRequest->setItems($items);

      return $this->subscriptionApi->updateSubscriptions($drSubscription->getId(), $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * delete a DrSubscription
   * @param string $id dr subscription id
   */
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

  /**
   * update a DrSubscription's source
   * @param string $id dr subscription id
   * @param string $sourceId dr source id
   */
  public function updateSubscriptionSource(string $id, string $sourceId)
  {
    try {
      $updateSubscriptionRequest = new  DrUpdateSubscriptionRequest();
      $updateSubscriptionRequest->setSourceId($sourceId);

      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * cancel a DrSubscription
   */
  public function cancelSubscription(string $id): DrSubscription
  {
    try {
      $updateSubscriptionRequest = new DrUpdateSubscriptionRequest();
      $updateSubscriptionRequest->setState('cancelled');

      return $this->subscriptionApi->updateSubscriptions($id, $updateSubscriptionRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * tax id
   */

  public function createTaxId(string $type, string $value): DrCustomerTaxIdentifier
  {
    try {
      $taxIdRequest = new DrTaxIdentifierRequest();
      $taxIdRequest->setType($type);
      $taxIdRequest->setValue($value);

      return $this->taxIdentifierApi->createTaxIdentifiers($taxIdRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function getTaxId(string $id): DrCustomerTaxIdentifier
  {
    try {
      return $this->taxIdentifierApi->retrieveTaxIdentifiers($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function deleteTaxId(string $id)
  {
    try {
      $this->taxIdentifierApi->deleteTaxIdentifiers($id);
      return true;
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      return false;
    }
  }

  /**
   * list customer tax ids
   *
   * @return DrCustomerTaxIdentifier[]
   */
  public function listCustomerTaxIds(string $customerId): array
  {
    try {
      return $this->taxIdentifierApi->listTaxIdentifiers(customer_id: $customerId)->getData() ?? [];
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  public function attachCustomerTaxId(string $customerId, string $taxId): DrTaxIdentifier
  {
    try {
      return $this->customerApi->createCustomerTaxIdentifier($customerId, $taxId);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * create a DrRefund
   */
  public function createRefund(Refund $refund): DrOrderRefund
  {
    $refundRequest = new DrRefundRequest();
    $refundRequest->setOrderId($refund->getDrOrderId());
    $refundRequest->setCurrency($refund->currency);
    $refundRequest->setAmount($refund->amount);
    $refundRequest->setReason($refund->reason ?? "");
    $refundRequest->setMetadata(['created_from' => 'siser-system']); // create from siser-system

    try {
      return $this->refundApi->createRefunds($refundRequest);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * get a DrRefund by id
   * @param string $id dr refund id
   */
  public function getRefund(string $id): DrOrderRefund
  {
    try {
      return $this->refundApi->retrieveRefunds($id);
    } catch (\Throwable $th) {
      Log::warning('DRAPI:' . $th->getMessage());
      throw $th;
    }
  }

  /**
   * list dr events
   *
   * @return DrEvent[]
   */
  public function listEvents()
  {
    try {
      return $this->eventApi->listEvents()->getData() ?? [];
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }

  /**
   * get dr events
   */
  public function getEvent(string $id): DrEvent
  {
    try {
      return $this->eventApi->retrieveEvents($id);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }


  /**
   * create file link from file id
   * @param string $fileId dr file id
   * @param Carbon $expiresTime link expires time
   */
  public function createFileLink(string $fileId, Carbon $expiresTime): DrFileLink
  {
    try {
      $fileLinkRequest = new DrFileLinkRequest();
      $fileLinkRequest->setFileId($fileId);
      $fileLinkRequest->setExpiresTime($expiresTime->toIso8601ZuluString()); // @phpstan-ignore-line

      return $this->fileLinkApi->createFileLinks($fileLinkRequest);
    } catch (\Throwable $th) {
      throw $this->throwException($th);
    }
  }
}
