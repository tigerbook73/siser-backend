<?php

namespace Tests\DR;

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
use Tests\DR\DrTestTrait;
use Tests\Models\Subscription as ModelsSubscription;

class DrApiTestCase extends ApiTestCase
{
  use DrTestTrait;

  /**
   * the followinig are test helpers
   */
  public function createOrUpdateBillingInfo()
  {
    // prepare
    $billingInfo = [
      'first_name'    => 'first_name',
      'last_name'     => 'last_name',
      'phone'         => '',
      'organization'  => '',
      'email'         => 'test-case@me.com',
      'address' => [
        'line1'       => '328 Reserve Road,  VIC',
        'line2'       => '',
        'city'        => 'Cheltenham',
        'postcode'    => '3192',
        'state'       => 'VIC',
        'country'     => 'AU',
      ]
    ];

    // mock up
    if (isset($this->user->dr['customer_id'])) {
      $this->mockUpdateCustomer();
    } else {
      $this->mockCreateCustomer();
    }

    // call api
    $response = $this->postJson('/api/v1/account/billing-info', $billingInfo);

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $response->assertSuccessful()->assertJson($billingInfo);
    $this->assertTrue(isset($this->user->dr['customer_id']));

    return $response;
  }

  public function createOrUpdatePaymentMethod()
  {
    // mock up
    $this->mockAttachCustomerSource();
    if ($this->user->payment_method->dr['source_id'] ?? null) {
      $this->mockDetachCustomerSourceAsync();
    }
    if ($activeSubscripiton = $this->user->getActiveLiveSubscription()) {
      $this->mockUpdateSubscriptionSource($activeSubscripiton);
    }

    $response = $this->postJson('/api/v1/account/payment-method', [
      'type' => 'creditCard',
      'dr' => ['source_id' => 'digital-river-source-id-master'],
    ]);

    // refresh authenticated user data
    $this->user->refresh();

    // assert 
    $response->assertSuccessful();
    $this->assertTrue(isset($this->user->payment_method->dr['source_id']));

    return $response;
  }

  public function createSubscription()
  {
    // mock up
    $this->mockCreateCheckout();

    // call api
    $response = $this->postJson('/api/v1/account/subscriptions', ['plan_id' => Plan::public()->first()->id]);

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $subscription = $this->user->getDraftSubscriptionById($response->json('id'));
    $this->assertTrue(!!$subscription);
    $this->assertTrue($subscription->status == 'draft');

    return $response;
  }

  public function deleteSubscription(int $id)
  {
    $subscription = Subscription::find($id);
    // mock up
    if (isset($subscription->dr['checkout_id'])) {
      $this->mockDeleteCheckoutAsync();
    }
    if (isset($subscription->dr['subscription_id'])) {
      $this->mockDeleteSubscriptionAsync();
    }

    // call api
    $response = $this->deleteJson("/api/v1/account/subscriptions/$id");

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $response->assertSuccessful();
    $subscription = $this->user->getDraftSubscriptionById($id);
    $this->assertTrue(!$subscription);

    return $response;
  }

  public function paySubscription(int $id, string $terms = 'this is test terms ...')
  {
    // prepare
    $subscription = $this->user->getDraftSubscriptionById($id);

    // mock up
    $this->mockAttachCheckoutSource();
    $this->mockUpdateCheckoutTerms($subscription);
    $this->mockConvertCheckoutToOrder($subscription);

    // call api
    $response = $this->postJson(
      "/api/v1/account/subscriptions/$subscription->id/pay",
      ['terms' => $terms]
    );

    // refresh data
    $subscription->refresh();

    // assert
    $this->assertTrue($subscription->status == 'pending');

    return $response;
  }

  public function onOrderAccept(Subscription $subscription): Subscription
  {
    // prepare
    $this->assertTrue($subscription->status == 'pending');

    // mock up
    $this->mockFulfillOrder();

    // call api
    $response = $this->sendOrderAccepted($this->drHelper->createOrder(
      $subscription,
      $subscription->dr['order_id'],
      DrOrder::STATE_ACCEPTED
    ));

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'processing');

    return $subscription;
  }

  public function onOrderComplete(Subscription $subscription): Subscription
  {
    // prepare
    $this->assertTrue($subscription->status == 'processing');

    // mock up
    $this->mockActivateSubscription($subscription);
    if ($previousSubscription = $subscription->user->getActiveLiveSubscription()) {
      $this->mockCancelSubscription($previousSubscription);
    }

    // call api
    $response = $this->sendOrderComplete($this->drHelper->createOrder(
      $subscription,
      null,
      DrOrder::STATE_COMPLETE
    ));

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active');
    $this->assertTrue($invoice->status == 'completing');

    return $subscription;
  }

  private function onPaymentFailed(Subscription $subscription, string $type)
  {
    // prepare
    $this->assertTrue($subscription->status == 'processing' || $subscription->status == 'pending');

    // mock up
    // null

    // call api
    if ($type == 'order.blocked') {
      $order = $this->drHelper->createOrder($subscription, null, DrOrder::STATE_BLOCKED);
      $response = $this->sendOrderBlocked($order);
    } else if ($type == 'order.cancelled') {
      $order = $this->drHelper->createOrder($subscription, null, DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderCancelled($order);
    } else if ($type == 'order.charge.failed') {
      $order = $this->drHelper->createOrder($subscription, null, DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderChargeFailed($order);
    } else if ($type == 'order.charge.capture.failed') {
      $order = $this->drHelper->createCharge($subscription->dr['order_id'], DrCharge::STATE_FAILED);
      $response = $this->sendOrderChargeCaptureFailed($order);
    }

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'failed');

    return $subscription;
  }

  public function onOrderBlocked(Subscription $subscription)
  {
    return $this->onPaymentFailed($subscription, 'order.blocked');
  }

  public function onOrderCancelled(Subscription $subscription)
  {
    return $this->onPaymentFailed($subscription, 'order.cancelled');
  }

  public function onOrderChargeFailed(Subscription $subscription)
  {
    return $this->onPaymentFailed($subscription, 'order.charge.failed');
  }

  public function onOrderChargeCaptureFailed(Subscription $subscription)
  {
    return $this->onPaymentFailed($subscription, 'order.charge.capture.failed');
  }

  public function onOrderInvoiceCompleted(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');
    $invoice = $subscription->getActiveInvoice();

    // mock up
    $this->mockGetOrder($subscription);
    $this->mockCreateFileLink();

    // call api
    $response = $this->sendOrderInvoiceCreated($this->drHelper->createOrder(
      $subscription,
      null,
      DrOrder::STATE_COMPLETE
    ));

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($invoice->status == 'completed');

    return $subscription;
  }

  public function onInvoiceOpen(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // call api
    $response = $this->sendInvoiceOpen($this->drHelper->createInvoice($subscription));

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($invoice->status == 'open');

    return $subscription;
  }

  public function onSubscriptionReminder(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // call api
    $response = $this->sendSubscriptionReminder($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($invoice == null);

    return $subscription;
  }

  public function onSubscriptionPaymentFailed(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // call api
    $response = $this->sendSubscriptionPaymentFailed($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'overdue');
    $this->assertTrue($invoice->status == 'overdue');

    return $subscription;
  }

  public function onSubscriptionExtended(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // call api
    $response = $this->sendSubscriptionExtended(
      $this->drHelper->createSubscription($subscription),
      $this->drHelper->createInvoice($subscription)
    );

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($invoice->status == 'completing');

    return $subscription;
  }

  public function onSubscriptionFailed(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // call api
    $response = $this->sendSubscriptionFailed($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();
    $invoice = $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'failed');
    $this->assertTrue($invoice->status == 'failed');

    return $subscription;
  }
}
