<?php

namespace Tests\DR;

use App\Models\BillingInfo;
use App\Models\Invoice;
use App\Models\ProductItem;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\TaxId;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Api\CheckoutsApi as DrCheckoutsApi;
use DigitalRiver\ApiSdk\Model\Address;
use DigitalRiver\ApiSdk\Model\Applicability;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CreditCard as DrCreditCard;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\Event;
use DigitalRiver\ApiSdk\Model\EventData;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\CustomerTaxIdentifier as DrCustomerTaxIdentifier;
use DigitalRiver\ApiSdk\Model\InvoiceItem;
use DigitalRiver\ApiSdk\Model\OrderItem;
use DigitalRiver\ApiSdk\Model\Payments;
use DigitalRiver\ApiSdk\Model\ProductDetails;
use DigitalRiver\ApiSdk\Model\RefundItem;
use DigitalRiver\ApiSdk\Model\Session;
use DigitalRiver\ApiSdk\Model\Shipping;
use DigitalRiver\ApiSdk\Model\SkuItem;
use DigitalRiver\ApiSdk\Model\SubscriptionInfo;
use DigitalRiver\ApiSdk\Model\SubscriptionItems;
use DigitalRiver\ApiSdk\Model\Tax;
use DigitalRiver\ApiSdk\Model\TaxIdentifier as DrTaxIdentifier;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Tests\DR\DrObject;


/**
 * @property DrCheckout[]  $drCheckouts
 * @property DrCustomer[]  $drCustomers
 * @property DrInvoice[]  $drInvoices
 * @property DrOrder[]  $drOrders
 * @property DrOrderRefund[]  $drRefunds
 * @property DrSource[]  $drSources
 * @property DrSubscription[]  $drSubscriptions
 */
class DrTestHelper
{
  public $drCheckouts = [];
  public $drCustomers = [];
  public $drFileLinks = [];
  public $drInvoices = [];
  public $drOrders = [];
  public $drRefunds = [];
  public $drSources = [];
  public $drSubscriptions = [];
  public $drTaxIds = [];

  public float $taxRate = 0.1;

  // cache
  public function __construct() {}

  protected function convertModel($object, string $model): mixed
  {
    $data = $object->jsonSerialize();

    // remove $data->state
    if (is_array($data)) {
      unset($data['state']);
    } else if (is_object($data)) {
      unset($data->state);
    }

    return DrObjectSerializer::deserialize($data, $model);
  }

  public function getTaxRate($taxIdInfo = null)
  {
    return ($taxIdInfo) ? 0 : $this->taxRate;
  }

  public function getDrCheckout(string|null $id): DrCheckout|null
  {
    return $this->drCheckouts[$id] ?? null;
  }

  protected function setDrCheckout(DrCheckout $drCheckout): self
  {
    $this->drCheckouts[$drCheckout->getId()] = $drCheckout;
    return $this;
  }

  protected function unsetDrCheckout(string $id): self
  {
    unset($this->drCheckouts[$id]);
    return $this;
  }

  public function getDrCustomer(string|null $id): DrCustomer|null
  {
    return $this->drCustomers[$id] ?? null;
  }

  protected function setDrCustomer(DrCustomer $drCustomer): self
  {
    $this->drCustomers[$drCustomer->getId()] = $drCustomer;
    return $this;
  }

  protected function unsetDrCustomer(string $id): self
  {
    unset($this->drCustomers[$id]);
    return $this;
  }

  public function getDrFileLink(string|null $id): DrFileLink|null
  {
    return $this->drFileLinks[$id] ?? null;
  }

  protected function setDrFileLink(DrFileLink $drFileLink): self
  {
    $this->drFileLinks[$drFileLink->getId()] = $drFileLink;
    return $this;
  }

  protected function unsetDrFileLink(string $id): self
  {
    unset($this->drFileLinks[$id]);
    return $this;
  }

  public function getDrInvoice(string|null $id): DrInvoice|null
  {
    return $this->drInvoices[$id] ?? null;
  }

  protected function setDrInvoice(DrInvoice $drInvoice): self
  {
    $this->drInvoices[$drInvoice->getId()] = $drInvoice;
    return $this;
  }

  protected function unsetDrInvoice(string $id): self
  {
    unset($this->drInvoices[$id]);
    return $this;
  }

  public function getDrOrder(string|null $id): DrOrder|null
  {
    return $this->drOrders[$id] ?? null;
  }

  protected function setDrOrder(DrOrder $drOrder): self
  {
    $this->drOrders[$drOrder->getId()] = $drOrder;
    return $this;
  }

  protected function unsetDrOrder(string $id): self
  {
    unset($this->drOrders[$id]);
    return $this;
  }

  public function getDrRefund(string|null $id): DrOrderRefund|null
  {
    return $this->drRefunds[$id] ?? null;
  }

  protected function setDrRefund(DrOrderRefund $drRefund): self
  {
    $this->drRefunds[$drRefund->getId()] = $drRefund;
    return $this;
  }

  protected function unsetDrRefund(string $id): self
  {
    unset($this->drRefunds[$id]);
    return $this;
  }

  public function getDrSource(string|null $id): DrSource|null
  {
    return $this->drSources[$id] ?? null;
  }

  protected function setDrSource(DrSource $drSource): self
  {
    $this->drSources[$drSource->getId()] = $drSource;
    return $this;
  }

  protected function unsetDrSource(string $id): self
  {
    unset($this->drSources[$id]);
    return $this;
  }

  public function getDrSubscription(string|null $id): DrSubscription|null
  {
    return $this->drSubscriptions[$id] ?? null;
  }

  protected function setDrSubscription(DrSubscription $drSubscription): self
  {
    $this->drSubscriptions[$drSubscription->getId()] = $drSubscription;
    return $this;
  }

  protected function unsetDrSubscription(string $id): self
  {
    unset($this->drSubscriptions[$id]);
    return $this;
  }

  public function getDrTaxId(string|null $id): DrCustomerTaxIdentifier
  {
    return $this->drTaxIds[$id] ?? null;
  }

  protected function setDrTaxId(DrCustomerTaxIdentifier $drTaxId): self
  {
    $this->drTaxIds[$drTaxId->getId()] = $drTaxId;
    return $this;
  }

  protected function unsetDrTaxId(string $id): self
  {
    unset($this->drTaxIds[$id]);
    return $this;
  }

  public function uuid()
  {
    return 'dr_' . uuid_create();
  }

  public function copyItemsFrom(
    DrOrder|DrInvoice|DrSubscription $to,
    DrCheckout|DrInvoice|Subscription $from,
    bool $subscriptionNext = false
  ) {
    if ($from instanceof Subscription) {
      $items = $subscriptionNext ? $from->next_invoice['items'] : $from->items;
      $drItems = [];
      foreach ($items as $item) {
        $productDetails = ProductItem::BuildDrProductDetails($item);

        // items
        if ($to instanceof DrSubscription) {
          $drItem = new SubscriptionItems();
          $drItem->setPrice($item['price']);
          $drItem->setQuantity(1);
          $drItem->setProductDetails($productDetails);
        } else {
          $drItem = ($to instanceof DrOrder) ? new OrderItem() : new InvoiceItem();
          $drItem->setAmount($item['price']);
          $drItem->setTax(
            (new Tax())
              ->setRate($this->getTaxRate($from->tax_id_info))
              ->setAmount($item['tax'])
          );
          $drItem->setQuantity(1);
          $drItem->setProductDetails($productDetails);
          $drItem->setSubscriptionInfo((new SubscriptionInfo())->setSubscriptionId($from->getDrSubscriptionId()));
          if ($drItem instanceof OrderItem) {
            $drItem->setAvailableToRefundAmount($drItem->getAmount());
            $drItem->setId($this->uuid());
          }
        }
        $drItems[] = $drItem;
      }
      $to->setItems($drItems);
      return $to;
    }

    if ($from instanceof DrCheckout || $from instanceof DrInvoice) {
      $drItems = [];
      foreach ($from->getItems() as $item) {
        $productDetails = (new ProductDetails())
          ->setDescription($item->getProductDetails()->getDescription())
          ->setName($item->getProductDetails()->getName())
          ->setCountryOfOrigin($item->getProductDetails()->getCountryOfOrigin())
          ->setSkuGroupId($item->getProductDetails()->getSkuGroupId());

        // item
        if ($to instanceof DrSubscription) {
          $drItem = new SubscriptionItems();
          $drItem->setPrice($item->getAmount());
          $drItem->setQuantity(1);
          $drItem->setProductDetails($productDetails);
        } else {
          $drItem = ($to instanceof DrOrder) ?  new OrderItem() : new InvoiceItem();;
          $drItem->setAmount($item->getAmount());
          $drItem->setTax(
            (new Tax())
              ->setRate($item->getTax()->getRate())
              ->setAmount($item->getTax()->getAmount())
          );
          $drItem->setQuantity(1);
          $drItem->setProductDetails($productDetails);
          $drItem->setSubscriptionInfo(
            (new SubscriptionInfo())->setSubscriptionId($item->getSubscriptionInfo()->getSubscriptionId())
          );
          if ($drItem instanceof OrderItem) {
            $drItem->setAvailableToRefundAmount($drItem->getAmount());
            $drItem->setId($this->uuid());
          }
        }
        $drItems[] = $drItem;
      }
      $to->setItems($drItems);
      return $to;
    }
    return $to;
  }

  public function createCharge(string $order_id = null, string $state = null): DrCharge
  {
    $charge = DrObject::charge();
    $charge->setId($this->uuid())
      ->setOrderId($order_id ?: $this->uuid())
      ->setState($state ?: 'complete');
    return $charge;
  }

  public function createCheckout(Invoice $invoice): DrCheckout
  {
    $tester = new TestDigitalRiverService();
    $checkout = $tester->createCheckout($invoice);
    $checkout->setId($this->uuid());
    $subscripitonId = $this->uuid();

    $drItems = [];
    foreach ($invoice->items as $item) {
      $productDetails = ProductItem::BuildDrProductDetails($item);

      $subscriptionInfo = new SubscriptionInfo();
      $subscriptionInfo->setSubscriptionId($subscripitonId);

      $drItem = new SkuItem();
      $drItem->setAmount($item['price']);
      $drItem->setTax(
        (new Tax())
          ->setRate($this->getTaxRate($invoice->tax_id_info))
          ->setAmount(round($item['price'] * $this->getTaxRate($invoice->tax_id_info), 2))
      );
      $drItem->setQuantity(1);
      $drItem->setProductDetails($productDetails);
      $drItem->setSubscriptionInfo($subscriptionInfo);
      $drItems[] = $drItem;
    }
    $checkout->setItems($drItems);

    $checkout->setSubtotal(ProductItem::calcTotal($invoice->items, 'price'));
    $checkout->setTotalTax(ProductItem::calcTotal($invoice->items, 'tax'));
    $checkout->setTotalAmount(round($checkout->getSubtotal() + $checkout->getTotalTax(), 2));
    $checkout->setPayment((new Payments())->setSession((new Session())->setId($this->uuid())));

    $this->setDrCheckout($checkout);

    $this->createSubscription($invoice->subscription, $subscripitonId);
    return $checkout;
  }

  public function retrieveTaxRate(User $user, TaxId $taxId = null): float
  {
    $checkout = DrObject::checkout();
    $checkout->getItems()[0]->setTax((new Tax())->setRate($this->getTaxRate($taxId?->info())));
    return $checkout->getItems()[0]->getTax()->getRate();
  }

  public function updateCheckoutTerms(string $checkoutId, string $terms): DrCheckout
  {
    $checkout = $this->getDrCheckout($checkoutId);
    $checkout->getItems()[0]->getSubscriptionInfo()->setTerms($terms);
    return $checkout;
  }

  public function deleteCheckout(string $id)
  {
    $this->unsetDrCheckout($id);
  }

  public function createCustomer(BillingInfo $billingInfo): DrCustomer
  {
    $customer = DrObject::customer();
    $customer->setId($this->uuid());
    $this->setDrCustomer($customer);

    $this->updateCustomer($customer->getId(), $billingInfo);
    return $customer;
  }

  public function updateCustomer(string $id, BillingInfo $billingInfo): DrCustomer
  {
    $customer = $this->getDrCustomer($id);
    if ($billingInfo) {
      $customer
        ->setEmail($billingInfo->email)
        ->setShipping(
          (new Shipping())->setAddress(
            (new Address())->setCountry($billingInfo->address['country'])
              ->setState($billingInfo->address['state'])
              ->setPostalCode($billingInfo->address['postcode'])
              ->setLine1($billingInfo->address['line1'])
              ->setLine2($billingInfo->address['line2'])
              ->setCity($billingInfo->address['city'])
          )
        )
        ->setType($billingInfo->customer_type);
    }
    return $customer;
  }

  public function attachCustomerSource(string $customerId, string $sourceId): DrSource
  {
    return $this->getDrSource($sourceId) ?? $this->createSource(id: $sourceId, customerId: $customerId);
  }

  public function attachCheckoutSource(string $checkoutId, string $sourceId): DrSource
  {
    $checkout = $this->getDrCheckout($checkoutId);
    $source = $this->getDrSource($sourceId);
    $checkout->getPayment()->setSources([$source->getId()]);
    return $source;
  }

  public function createFulfillment(string $id = null): DrFulfillment
  {
    $fulfillment = DrObject::fulfillment();
    $fulfillment->setId($id ?: $this->uuid());
    return $fulfillment;
  }

  public function getSource(string $id): DrSource
  {
    return $this->getDrSource($id);
  }

  /**
   * create renew dr invoice
   */
  public function createInvoice(Subscription $subscription,  string $order_id = null): DrInvoice
  {
    $drCheckout = $this->getDrCheckout($subscription->getDrCheckoutId());

    /** @var DrInvoice $invoice */
    $invoice = $this->convertModel($drCheckout, DrInvoice::class);
    $invoice->setId($this->uuid());

    $this->copyItemsFrom($invoice, $subscription, subscriptionNext: true);

    $invoice->setSubtotal($subscription->next_invoice['subtotal']);
    $invoice->setTotalTax($subscription->next_invoice['total_tax']);
    $invoice->setTotalAmount($subscription->next_invoice['total_amount']);
    $invoice->setState(DrInvoice::STATE_DRAFT);
    if ($order_id) {
      $invoice->setOrderId($order_id);
    }
    $this->setDrInvoice($invoice);
    return $invoice;
  }

  public function createInvoiceOrder(DrInvoice $invoice): DrOrder
  {
    /** @var DrOrder $order */
    $order = $this->convertModel($invoice, DrOrder::class);

    $order->setId($this->uuid());
    $order->setState(DrOrder::STATE_COMPLETE);
    return $order;
  }

  /**
   * create renew order from dr invoice
   */
  public function createOrderFromInvoice(Invoice $invoice): DrOrder
  {
    $drInvoice = $this->getDrInvoice($invoice->getDrInvoiceId());

    /** @var DrOrder $order */
    $order = $this->convertModel($drInvoice, DrOrder::class);
    $order->setId($this->uuid());
    $order->setUpstreamId($invoice->id);
    $order->setState(DrOrder::STATE_ACCEPTED);
    $order->setRefundedAmount(0);
    $order->setAvailableToRefundAmount($invoice->total_amount);
    $this->copyItemsFrom($order, $drInvoice);

    $this->setDrOrder($order);
    return $order;
  }

  public function getOrder(string $id): DrOrder
  {
    return $this->getDrOrder($id);
  }

  public function updateOrderUpstreamId(string $id, string|int $upstreamId): DrOrder
  {
    $order = $this->getDrOrder($id);
    $order->setUpstreamId($upstreamId);
    return $order;
  }

  public function convertCheckoutToOrder(string $checkoutId, string $state = DrOrder::STATE_ACCEPTED): DrOrder
  {
    $checkout = $this->getDrCheckout($checkoutId);

    /** @var DrOrder $order */
    $order = $this->convertModel($checkout, DrOrder::class);
    $order->setId($this->uuid());
    $order->setCheckoutId($checkout->getId());

    $this->copyItemsFrom($order, $checkout);

    $order->setSubtotal($checkout->getSubtotal());
    $order->setTotalTax($checkout->getTotalTax());
    $order->setTotalAmount($checkout->getTotalAmount());
    $order->setState($state);
    $order->setRefundedAmount(0);
    $order->setAvailableToRefundAmount($checkout->getTotalAmount());
    $this->copyItemsFrom($order, $checkout);

    $this->setDrOrder($order);
    return $order;
  }

  public function fulfillOrder(string $orderId, DrOrder $order = null, bool $cancel = false): DrFulfillment
  {
    $newFulfillment = $this->createFulfillment($orderId);
    $order = $this->getDrOrder($orderId);
    $order->setState($cancel ? DrOrder::STATE_CANCELLED : DrOrder::STATE_FULFILLED);
    return $newFulfillment;
  }

  public function createSource(string $id = null, string $type = null, string $lastFour = '9876', string $customerId = null, int $expireMonth = 12, int $expireYear = 2099): DrSource
  {
    $source = DrObject::source();
    $source->setId($id ?: $this->uuid())
      ->setType($type ?: 'creditCard')
      ->setCreditCard((new DrCreditCard())
        ->setBrand('visa')
        ->setLastFourDigits($lastFour)
        ->setExpirationMonth($expireMonth)
        ->setExpirationYear($expireYear))
      ->setCustomerId($customerId ?: $this->uuid());
    $this->setDrSource($source);
    return $source;
  }

  public function createFileLink(string $url = null): DrFileLink
  {
    $fileLink = DrObject::fileLink();
    $fileLink->setUrl($url ?: '/favicon.ico');
    return $fileLink;
  }

  public function getSubscription(string $id): DrSubscription
  {
    return $this->getDrSubscription($id);
  }

  public function createSubscription(Subscription $subscription, string $id): DrSubscription
  {
    $drSubscription = DrObject::subscription();
    $drSubscription->setId($id ?? $subscription->getDrSubscriptionId() ?? $this->uuid());
    $this->setDrSubscription($drSubscription);

    $this->copyItemsFrom($drSubscription, $subscription);

    return $drSubscription;
  }

  public function deleteSubscription(string $id): void
  {
    unset($this->drSubscriptions[$id]);
  }

  public function activateSubscription(string $id): DrSubscription
  {
    /** @var Subscription $subscription */
    $subscription = Subscription::where('dr_subscription_id', $id)->first();
    $updatedSubscription = $this->getDrSubscription($id);
    $updatedSubscription
      ->setCurrentPeriodStartDate(now())
      ->setCurrentPeriodEndDate(now()->addUnit(
        $subscription->isFreeTrial() ? $subscription->coupon_info['interval'] : $subscription->plan_info['interval'],
        $subscription->isFreeTrial() ? $subscription->coupon_info['interval_count'] : $subscription->plan_info['interval_count']
      ))
      ->setNextInvoiceDate(
        Carbon::parse($updatedSubscription->getCurrentPeriodEndDate())->subDays(1)
      )
      ->setNextReminderDate(
        Carbon::parse($updatedSubscription->getCurrentPeriodEndDate())->subDays(8)
      );
    return $updatedSubscription;
  }

  public function convertSubscriptionToNext(DrSubscription $drSubscription, Subscription $subscription): DrSubscription
  {
    $nextInvoice = $subscription->next_invoice;

    // update dr plan if requied
    $newDrPlanId = SubscriptionPlan::findNormalPlanDrId(
      $nextInvoice['plan_info']['interval'],
      $nextInvoice['plan_info']['interval_count']
    );
    $drSubscription->setPlanId($newDrPlanId);

    // update items
    $this->copyItemsFrom($drSubscription, $subscription, subscriptionNext: true);

    return $drSubscription;
  }

  public function updateSubscriptionSource(string $id, string $sourceId)
  {
    $drSubscription = $this->getDrSubscription($id);
    return $drSubscription->setSourceId($sourceId);
  }

  public function cancelSubscription(string $id): DrSubscription
  {
    $drSubscription = $this->getDrSubscription($id);
    return $drSubscription->setState('cancelled');
  }

  public function createTaxId(string $type, string $value): DrCustomerTaxIdentifier
  {
    $taxId = new DrCustomerTaxIdentifier();
    $taxId->setId($this->uuid());
    $taxId->setType($type);
    $taxId->setValue($value);
    $taxId->setState(DrCustomerTaxIdentifier::STATE_PENDING);
    $taxId->setApplicability([new Applicability()]);
    $this->setDrTaxId($taxId);
    return $taxId;
  }

  public function getTaxId(string $id): DrCustomerTaxIdentifier
  {
    return $this->getDrTaxId($id);
  }

  public function deleteTaxId(string $id)
  {
    $this->unsetDrTaxId($id);
  }

  public function attachCustomerTaxId(string $customerId, string $taxId): DrTaxIdentifier
  {
    $drCustomer = $this->getDrCustomer($customerId);
    $drCustomerTaxId = $this->getTaxId($taxId);

    $drCustomer->setTaxIdentifiers([$drCustomerTaxId]);
    $drCustomerTaxId->setCustomerId($customerId);
    $drCustomerTaxId->setApplicability([
      (new Applicability())
        ->setCountry($drCustomer->getShipping()->getAddress()->getCountry())
        ->setEntity('DR_IRELAND-ENTITY')
        ->setCustomerType($drCustomer->getType())
    ]);
    return $this->convertModel($drCustomerTaxId, DrTaxIdentifier::class);
  }

  public function createEvent(string $eventType, object|array $object, string $id = null): array
  {
    $data = (new EventData())->setObject($object);
    $event = (new Event())->setId($id ?? $this->uuid())
      ->setType($eventType)
      ->setData($data);

    return json_decode(json_encode($event->jsonSerialize()), true);
  }

  public function createOrderRefund(Refund $refund): DrOrderRefund
  {
    $drOrderRefund = new DrOrderRefund();
    $drOrderRefund->setId($refund->getDrRefundId() ?? $this->uuid())
      ->setOrderId($refund->getDrOrderId())
      ->setCurrency($refund->currency)
      ->setReason($refund->reason)
      ->setState(DrOrderRefund::STATE_PENDING)
      ->setMetadata([
        'created_from' => 'siser-system',
        'item_type' => $refund->item_type
      ])
    ;

    // item level refund or order level refund
    if ($refund->item_type == Refund::ITEM_LICENSE) {
      $item = $refund->items[0];
      $drOrderRefund->setItems([
        (new RefundItem())
          ->setItemId($item['dr_item_id'])
          ->setQuantity($item['quantity'])
          ->setAmount($item['available_to_refund_amount'])
      ]);
    } else {
      $drOrderRefund->setAmount($refund->amount);
    }

    $this->setDrRefund($drOrderRefund);

    // update dr order's availableToRefundAmount
    $drOrder = $this->getDrOrder($refund->getDrOrderId());
    $drOrder->setAvailableToRefundAmount($drOrder->getAvailableToRefundAmount() - $refund->amount);

    return $drOrderRefund;
  }

  /**
   * create a DrRefund
   */
  public function createRefund(Refund $refund): DrOrderRefund
  {
    return $this->createOrderRefund($refund);
  }

  public function createChargeBackRefund(Invoice $invoice): DrOrderRefund
  {
    $drRefund = new DrOrderRefund();
    $drRefund->setOrderId($invoice->getDrOrderId())
      ->setCurrency($invoice->currency)
      ->setAmount($invoice->total_amount)
      ->setReason('charge back')
      ->setState(DrOrderRefund::STATE_PENDING)
      ->setId($this->uuid());
    $this->setDrRefund($drRefund);
    return $drRefund;
  }
}

class TestDigitalRiverService extends DigitalRiverService
{
  public function __construct()
  {
    $this->checkoutApi = new class extends DrCheckoutsApi
    {
      public function createCheckouts($checkout_request = null, string $contentType = '')
      {
        $data = $checkout_request->jsonSerialize();
        return DrObjectSerializer::deserialize($data, DrCheckout::class);
      }
    };
  }
}
