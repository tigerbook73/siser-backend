<?php

namespace Tests\Feature;

use App\Models\Plan;
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
use Tests\ApiTestCase;
use Tests\DR\DrApiTestCase;
use Tests\DR\DrTestTrait;
use Tests\Models\Subscription as ModelsSubscription;

class AccountSubscriptionTestCase extends DrApiTestCase
{
  use DrTestTrait;

  public string $baseUrl = '/api/v1/account/subscriptions';
  public string $model = Subscription::class;

  public Subscription $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsSubscription);
  }
}
