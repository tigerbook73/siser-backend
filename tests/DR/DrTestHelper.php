<?php

namespace Tests\DR;

use App\Models\BillingInfo;
use App\Models\Refund;
use App\Models\Subscription;
use App\Services\DigitalRiver\DigitalRiverService;
use DigitalRiver\ApiSdk\Api\CheckoutsApi as DrCheckoutsApi;
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
use DigitalRiver\ApiSdk\Model\Payments;
use DigitalRiver\ApiSdk\Model\ProductDetails;
use DigitalRiver\ApiSdk\Model\Session;
use DigitalRiver\ApiSdk\Model\SkuItem;
use DigitalRiver\ApiSdk\Model\Tax;
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
  public function __construct()
  {
  }

  protected function convertModel($object, string $model)
  {
    $data = $object->jsonSerialize();
    return DrObjectSerializer::deserialize($data, $model);
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

  public function createCharge(string $order_id = null, string $state = null): DrCharge
  {
    $charge = DrObject::charge();
    $charge->setId($this->uuid())
      ->setOrderId($order_id ?: $this->uuid())
      ->setState($state ?: 'complete');
    return $charge;
  }

  public function createCheckout(Subscription $subscription): DrCheckout
  {
    $tester = new TestDigitalRiverService();
    $checkout = $tester->createCheckout($subscription);
    $checkout->setId($this->uuid());
    $subscripitonId = $this->uuid();
    $checkout->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($subscripitonId);
    $checkout->getItems()[0]->setTax((new Tax())->setRate($this->taxRate));
    $checkout->setSubtotal($subscription->price);
    $checkout->setTotalTax($checkout->getSubtotal() * $this->taxRate);
    $checkout->setTotalAmount($checkout->getSubtotal() + $checkout->getTotalTax());
    $checkout->setPayment((new Payments())->setSession((new Session())->setId($this->uuid())));

    $this->setDrCheckout($checkout);

    $this->createSubscription($subscription, $subscripitonId);
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
    $customer->setEmail($billingInfo->email);
    $this->setDrCustomer($customer);
    return $customer;
  }

  public function updateCustomer(string $id, BillingInfo $billingInfo = null): DrCustomer
  {
    $customer = $this->getDrCustomer($id);
    if ($billingInfo) {
      $customer->setEmail($billingInfo->email);
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

  /**
   * create renew dr invoice
   */
  public function createInvoice(Subscription $subscription,  string $order_id = null): DrInvoice
  {
    $drCheckout = $this->getDrCheckout($subscription->getDrCheckoutId());

    /** @var DrInvoice $invoice */
    $invoice = $this->convertModel($drCheckout, DrInvoice::class);
    $invoice->setId($this->uuid());

    $invoice->setSubtotal($subscription->next_invoice['subtotal']);
    $invoice->setTotalTax($subscription->next_invoice['total_tax']);
    $invoice->setTotalAmount($subscription->next_invoice['total_amount']);
    $invoice->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($subscription->id);
    if ($order_id) {
      $invoice->setOrderId($order_id);
    }
    return $invoice;
  }

  public function createOrder(Subscription $subscription, string $state = DrOrder::STATE_COMPLETE): DrOrder
  {
    $order = DrObject::order();
    $order->setId($id ?? $this->uuid());
    $order->setUpstreamId($subscription->getActiveInvoice()?->id);
    $order->setCheckoutId($subscription->getDrCheckoutId() ?? $this->uuid());

    $order->setSubtotal($subscription->price);
    $order->getItems()[0]->getTax()->setRate(0.1);
    $order->setTotalTax($order->getSubtotal() * 0.1);
    $order->setTotalAmount($order->getSubtotal() + $order->getTotalTax());

    $order->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($subscription->getDrSubscriptionId() ?? $this->uuid());
    $order->setState($state);
    $this->setDrOrder($order);
    return $order;
  }


  public function convertChekcoutToOrder(string $checkoutId, string $state = DrOrder::STATE_ACCEPTED): DrOrder
  {
    $checkout = $this->getDrCheckout($checkoutId);

    /** @var DrOrder $order */
    $order = $this->convertModel($checkout, DrOrder::class);
    $order->setId($this->uuid());
    $order->setCheckoutId($checkout->getId());

    $order->setSubtotal($checkout->getSubtotal());
    $order->setItems($checkout->getItems());
    $order->setTotalTax($checkout->getTotalTax());
    $order->setTotalAmount($checkout->getTotalAmount());
    $order->setState($state);
    $this->setDrOrder($order);
    return $order;
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

  public function createSubscription(Subscription $subscription, string $id): DrSubscription
  {
    $drSubscription = DrObject::subscription();
    $drSubscription->setId($id ?? $subscription->getDrSubscriptionId() ?? $this->uuid());
    $this->setDrSubscription($drSubscription);
    return $drSubscription;
  }

  public function deleteSubscription(string $id): void
  {
    unset($this->drSubscriptions[$id]);
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
      ->setCurrency($refund->currency)
      ->setAmount($refund->amount)
      ->setReason($refund->reason)
      ->setState($refund->status);
    $this->setDrRefund($drOrderRefund);
    return $drOrderRefund;
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
