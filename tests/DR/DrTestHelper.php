<?php

namespace Tests\DR;

use App\Models\BillingInfo;
use App\Models\Subscription;
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
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use Tests\DR\DrObject;


/**
 * internal class
 */
class DrTestHelper
{
  public function uuid()
  {
    return 'dr_' . uuid_create();
  }

  public function createCharge(string $order_id = null, string $state = null)
  {
    $charge = DrObject::charge();
    $charge->setId($this->uuid())
      ->setOrderId($order_id ?: $this->uuid())
      ->setState($state ?: 'complete');
    return $charge;
  }

  public function createCheckout(Subscription $subscription, string $id = null)
  {
    $checkout = DrObject::checkout();
    $checkout->setId($id ?? $subscription->dr['checkout_id'] ?? $this->uuid());

    $checkout->setCustomerId($subscription->user->dr['customer_id'] ?? $this->uuid());
    $checkout->setEmail($subscription->billing_info['email']);
    $checkout->setCurrency($subscription->currency);
    $checkout->setMetadata(['subscription_id' => $subscription->id]);
    $checkout->setUpstreamId($subscription->getActiveInvoice()->id);

    $checkout->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($this->uuid());

    $checkout->setSubtotal($subscription->price);
    $checkout->getItems()[0]->getTax()->setRate(0.1);
    $checkout->setTotalTax($checkout->getSubtotal() * 0.1);
    $checkout->setTotalAmount($checkout->getSubtotal() + $checkout->getTotalTax());

    $checkout->getPayment()->getSession()->setId($id ?? $subscription->dr['checkout_payment_session_id'] ?? $this->uuid());

    return $checkout;
  }

  public function createCustomer(string $id = null, BillingInfo $billingInfo = null)
  {
    $customer = DrObject::customer();
    $customer->setId($id ?: $this->uuid());
    if ($billingInfo) {
      $customer->setEmail($billingInfo->email);
    }
    return $customer;
  }

  public function createFulfillment(string $id = null)
  {
    $fulfillment = DrObject::fulfillment();
    $fulfillment->setId($id ?: $this->uuid());
    return $fulfillment;
  }

  public function createInvoice(Subscription $subscription, string $id = null, string $order_id = null)
  {
    $invoice = DrObject::invoice();
    $invoice->setId($id ?: $this->uuid());
    $invoice->setSubtotal($subscription->price);
    $invoice->getItems()[0]->getTax()->setRate(0.1);
    $invoice->setTotalTax($invoice->getSubtotal() * 0.1);
    $invoice->setTotalAmount($invoice->getSubtotal() + $invoice->getTotalTax());
    $invoice->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($subscription->id);
    if ($order_id) {
      $invoice->setOrderId($order_id);
    }
    return $invoice;
  }

  public function createOrder(Subscription $subscription, string $id = null, string $state = DrOrder::STATE_COMPLETE)
  {
    $order = DrObject::order();
    $order->setId($id ?? $this->uuid());
    $order->setUpstreamId($subscription->getActiveInvoice()?->id);

    $order->setSubtotal($subscription->price);
    $order->getItems()[0]->getTax()->setRate(0.1);
    $order->setTotalTax($order->getSubtotal() * 0.1);
    $order->setTotalAmount($order->getSubtotal() + $order->getTotalTax());

    $order->getItems()[0]->getSubscriptionInfo()->setSubscriptionId($subscription->getDrSubscriptionId() ?? $this->uuid());
    $order->setState($state);
    return $order;
  }

  public function createSource(string $id = null, string $type = null, string $lastFour = '9876', string $customerId = null)
  {
    $source = DrObject::source();
    $source->setId($id ?: $this->uuid())
      ->setType($type ?: 'creditCard')
      ->setCreditCard((new DrCreditCard())
        ->setBrand('visa')
        ->setLastFourDigits($lastFour))
      ->setCustomerId($customerId ?: $this->uuid());
    return $source;
  }

  public function createFileLink(string $url = null)
  {
    $fileLink = DrObject::fileLink();
    $fileLink->setUrl($url ?: '/favicon.ico');
    return $fileLink;
  }

  public function createSubscription(Subscription $subscription, string $id = null)
  {
    $drSubscripiton = DrObject::subscription();
    $drSubscripiton->setId($id ?? $subscription->dr['subscription_id'] ?? $this->uuid());

    return $drSubscripiton;
  }

  public function createEvent(string $eventType, object|array $object, string $id = null): array
  {
    $data = (new EventData())->setObject($object);
    $event = (new Event())->setId($id ?? $this->uuid())
      ->setType($eventType)
      ->setData($data);

    return json_decode(json_encode($event->jsonSerialize()), true);
  }
}
