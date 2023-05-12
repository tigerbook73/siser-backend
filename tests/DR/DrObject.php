<?php

namespace Tests\DR;

use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\ObjectSerializer  as DrObjectSerializer;

class DrObject
{
  static public function charge(): DrCharge
  {
    return new DrCharge();
  }

  static public function checkout(): DrCheckout
  {
    $objectJson = include __DIR__ . '/dr-checkout.php';
    $object = DrObjectSerializer::deserialize($objectJson, DrCheckout::class);
    return $object;
  }

  static public function customer(): DrCustomer
  {
    return new DrCustomer();
  }

  static public function fileLink(): DrFileLink
  {
    return new DrFileLink();
  }

  static public function fulfillment(): DrFulfillment
  {
    return new DrFulfillment();
  }

  static public function invoice(): DrInvoice
  {
    $objectJson = include __DIR__ . '/dr-invoice.php';
    $object = DrObjectSerializer::deserialize($objectJson, DrInvoice::class);
    return $object;
  }

  static public function order(): DrOrder
  {
    $objectJson = include __DIR__ . '/dr-order.php';
    $object = DrObjectSerializer::deserialize($objectJson, DrOrder::class);
    return $object;
  }

  static public function source(): DrSource
  {
    $objectJson = include __DIR__ . '/dr-source.php';
    $object = DrObjectSerializer::deserialize($objectJson, DrSource::class);
    return $object;
  }

  static public function subscription(): DrSubscription
  {
    $objectJson = include __DIR__ . '/dr-subscription.php';
    $object = DrObjectSerializer::deserialize($objectJson, DrSubscription::class);
    return $object;
  }
}
