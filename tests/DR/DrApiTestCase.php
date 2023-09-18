<?php

namespace Tests\DR;

use App\Models\Base\BillingInfo;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\DigitalRiverService;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CreditCard as DrCreditCard;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\Event as DrEvent;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\OrderRefund as DrOrderRefund;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use DigitalRiver\ApiSdk\Model\SubscriptionItems;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\ApiTestCase;

/**
 * @property DrCheckout[]  $drCheckouts 
 * @property DrCustomer[]  $drCustomers 
 * @property DrInvoice[]  $drInvoices 
 * @property DrOrder[]  $drOrders 
 * @property DrOrderRefund[]  $drRefunds 
 * @property DrSource[]  $drSources 
 * @property DrSubscription[]  $drSubscriptions 
 */
class DrApiTestCase extends ApiTestCase
{
  public DrTestHelper $drHelper;
  public MockInterface $drMock;

  // cache
  public $drCheckouts;
  public $drCustomers;
  public $drInvoices;
  public $drOrders;
  public $drRefunds;
  public $drSources;
  public $drSubscriptions;

  /**
   * setup function
   */
  protected function setUp(): void
  {
    parent::setUp();

    $this->drHelper = new DrTestHelper();
    $this->drMock = $this->mock(
      DigitalRiverService::class
    );

    $this->drCheckouts = [];
    $this->drCustomers = [];
    $this->drInvoices = [];
    $this->drOrders = [];
    $this->drRefunds = [];
    $this->drSources = [];
    $this->drSubscriptions = [];
  }

  protected function getDrCustomer(string $id)
  {
    return $this->drCustomers[$id] ?? null;
  }

  protected function setDrCustomer(DrCustomer $drCustomer)
  {
    return $this->drCustomers[$drCustomer->getId()] = $drCustomer;
  }

  protected function getDrCheckout(string $id)
  {
    return $this->drCheckouts[$id] ?? null;
  }

  protected function setDrCheckout(DrCheckout $drCheckout)
  {
    return $this->drCheckouts[$drCheckout->getId()] = $drCheckout;
  }

  protected function getDrOrder(string $id)
  {
    return $this->drOrders[$id] ?? null;
  }

  protected function setDrOrder(DrOrder $drOrder)
  {
    return $this->drOrders[$drOrder->getId()] = $drOrder;
  }

  protected function getDrSubscription(string $id)
  {
    return $this->drSubscriptions[$id] ?? null;
  }

  protected function setDrSubscription(DrSubscription $drSubscription)
  {
    return $this->drSubscriptions[$drSubscription->getId()] = $drSubscription;
  }

  protected function getDrSource(string $id)
  {
    return $this->drSources[$id] ?? null;
  }

  protected function setDrSource(DrSource $drSource)
  {
    return $this->drSources[$drSource->getId()] = $drSource;
  }

  protected function getDrInvoice(string $id)
  {
    return $this->drInvoices[$id] ?? null;
  }

  protected function setDrInvoice(DrInvoice $drInvoice)
  {
    return $this->drInvoices[$drInvoice->getId()] = $drInvoice;
  }

  protected function getDrRefund(string $id)
  {
    return $this->drRefunds[$id] ?? null;
  }

  protected function setDrRefund(DrOrderRefund $drRefund)
  {
    return $this->drRefunds[$drRefund->getId()] = $drRefund;
  }

  /**
   * The followings are DR API mock helper
   */
  public function mockGetCustomer(): self
  {
    $this->drMock
      ->shouldReceive('getCustomer')
      ->once()
      ->andReturnUsing(
        function (string $id): DrCustomer {
          return $this->getDrCustomer($id) ?? $this->setDrCustomer($this->drHelper->createCustomer(id: $id));
        }
      );
    return $this;
  }

  public function mockCreateCustomer(): self
  {
    $this->drMock
      ->shouldReceive('createCustomer')
      ->once()
      ->andReturnUsing(
        function (BillingInfo $billingInfo): DrCustomer {
          return $this->setDrCustomer($this->drHelper->createCustomer(billingInfo: $billingInfo));
        }
      );
    return $this;
  }

  public function mockUpdateCustomer(): self
  {
    $this->drMock
      ->shouldReceive('updateCustomer')
      ->once()
      ->andReturnUsing(
        function (string $id, BillingInfo $billingInfo): DrCustomer {
          $updatedCustomer = $this->getDrCustomer($id) ?? $this->setDrCustomer($this->drHelper->createCustomer(id: $id, billingInfo: $billingInfo));
          return $updatedCustomer->setEmail($billingInfo->email);
        }
      );
    return $this;
  }

  public function mockAttachCustomerSource(): self
  {
    $this->drMock
      ->shouldReceive('attachCustomerSource')
      ->once()
      ->andReturnUsing(
        function (string $customerId, string $sourceId): DrSource {
          return $this->getDrSource($sourceId) ?? $this->setDrSource($this->drHelper->createSource(id: $sourceId, customerId: $customerId));
        }
      );
    return $this;
  }

  public function mockDetachCustomerSource(bool $result = true): self
  {
    $this->drMock
      ->shouldReceive('detachCustomerSource')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockGetCheckout(): self
  {
    $this->drMock
      ->shouldReceive('getCheckout')
      ->once()
      ->andReturnUsing(
        function (string $id): DrCheckout {
          return $this->getDrCheckout($id);
        }
      );

    return $this;
  }

  public function mockCreateCheckout(): self
  {
    $this->drMock
      ->shouldReceive('createCheckout')
      ->once()
      ->andReturnUsing(
        function (Subscription $subscription): DrCheckout {
          $newCheckout = $this->drHelper->createCheckout($subscription, null);
          $this->drCheckouts[$newCheckout->getId()] = $newCheckout;

          $drSubscription = $this->drHelper->createSubscription(
            $subscription,
            $newCheckout->getItems()[0]->getSubscriptionInfo()->getSubscriptionId()
          );
          $this->setDrSubscription($drSubscription);
          return $newCheckout;
        }
      );
    return $this;
  }

  public function mockUpdateCheckoutTerms(): self
  {
    $this->drMock
      ->shouldReceive('updateCheckoutTerms')
      ->once()
      ->andReturnUsing(
        function (string $checkoutId, string $terms): DrCheckout {
          $updatedCheckout = $this->getDrCheckout($checkoutId);
          $updatedCheckout->getItems()[0]->getSubscriptionInfo()->setTerms($terms);
          return $updatedCheckout;
        }
      );
    return $this;
  }

  public function mockDeleteCheckout(bool $result = true): self
  {
    $this->drMock
      ->shouldReceive('deleteCheckout')
      ->once()
      ->andReturnUsing(
        function (string $checkoutId) use ($result) {
          if ($result) {
            unset($this->drCheckouts[$checkoutId]);
          }
          return $result;
        }
      );
    return $this;
  }

  public function mockAttachCheckoutSource(): self
  {
    $this->drMock
      ->shouldReceive('attachCheckoutSource')
      ->once()
      ->andReturnUsing(
        function (string $checkoutId, string $sourceId): DrSource {
          $newSource = $this->drSources[$sourceId] ?? $this->drHelper->createSource(id: $sourceId);
          $this->drSources[$sourceId] = $newSource;
          return $newSource;
        }
      );
    return $this;
  }

  public function mockGetSource(): self
  {
    $this->drMock
      ->shouldReceive('getSource')
      ->once()
      ->andReturnUsing(
        function (string $sourceId): DrSource {
          return $this->drSources[$sourceId] ?? $this->drSources[$sourceId] = $this->drHelper->createSource(id: $sourceId);
        }
      );
    return $this;
  }

  public function mockGetOrder(): self
  {
    $this->drMock
      ->shouldReceive('getOrder')
      ->once()
      ->andReturnUsing(
        function (string $id): DrOrder {
          return $this->drOrders[$id];
        }
      );
    return $this;
  }

  public function mockConvertCheckoutToOrder(Subscription $subscription, string $state = DrOrder::STATE_ACCEPTED): self
  {
    $this->drMock
      ->shouldReceive('convertCheckoutToOrder')
      ->once()
      ->andReturnUsing(
        function (string $checkoutId) use ($subscription, $state): DrOrder {
          $newOrder = $this->drHelper->createOrder($subscription, $checkoutId, $state);
          $this->drOrders[$newOrder->getId()] = $newOrder;
          return $newOrder;
        }
      );

    return $this;
  }

  public function mockUpdateOrderUpstreamId(): self
  {
    $this->drMock
      ->shouldReceive('updateOrderUpstreamId')
      ->once()
      ->andReturnUsing(
        function (string $orderId, string|int $upstreamId): DrOrder {
          $updatedOrder = $this->drOrders[$orderId];
          $updatedOrder->setUpstreamId($upstreamId);
          return $updatedOrder;
        }
      );

    return $this;
  }

  public function mockFulfillOrder(): self
  {
    $this->drMock
      ->shouldReceive('fulfillOrder')
      ->once()
      ->andReturnUsing(
        function (string $orderId, DrOrder $order = null, bool $cancel = false): DrFulfillment {
          $newFulfillment = $this->drHelper->createFulfillment($orderId);
          $order = $this->drOrders[$orderId];
          $order->setState($cancel ? DrOrder::STATE_CANCELLED : DrOrder::STATE_FULFILLED);
          return $newFulfillment;
        }
      );

    return $this;
  }

  public function mockGetSubscription(): self
  {
    $this->drMock
      ->shouldReceive('getSubscription')
      ->once()
      ->andReturnUsing(
        function (string $id): DrSubscription {
          return $drSubscription ?? $this->drSubscriptions[$id];
        }
      );
    return $this;
  }

  public function mockActivateSubscription(): self
  {
    $this->drMock
      ->shouldReceive('activateSubscription')
      ->once()
      ->andReturnUsing(
        function (string $id): DrSubscription {
          $updatedSubscription = $this->drSubscriptions[$id];
          $updatedSubscription
            ->setCurrentPeriodEndDate(now()->addDays(config('dr.dr_test.interval_count')))
            ->setNextInvoiceDate(
              now()->addDays(config('dr.dr_test.interval_count') - config('dr.dr_test.billing_offset_days'))
            );
          return $updatedSubscription;
        }
      );
    return $this;
  }

  public function mockDeleteSubscription(bool $result = false): self
  {
    $this->drMock
      ->shouldReceive('deleteSubscription')
      ->once()
      ->andReturnUsing(
        function (string $id) use ($result) {
          if ($result) {
            unset($this->drSubscriptions[$id]);
          }
          return $result;
        }
      );
    return $this;
  }

  public function mockUpdateSubscriptionSource(): self
  {
    $this->drMock
      ->shouldReceive('updateSubscriptionSource')
      ->once()
      ->andReturnUsing(
        function (string $id, string $sourceId): DrSubscription {
          $updatedSubscription = $this->drSubscriptions[$id];
          $updatedSubscription->setSourceId($sourceId);
          return $updatedSubscription;
        }
      );
    return $this;
  }

  public function mockUpdateSubscriptionItems(): self
  {
    $this->drMock
      ->shouldReceive('updateSubscriptionItems')
      ->once()
      ->andReturnUsing(
        function (string $id, Subscription $subscription): DrSubscription {
          $updatedSubscription = $this->drSubscriptions[$id];
          $this->drSubscriptions[$updatedSubscription->getId()] = $updatedSubscription;
          // TODO: update items
          return $updatedSubscription;
        }
      );
    return $this;
  }

  public function mockCancelSubscription(): self
  {
    $this->drMock
      ->shouldReceive('cancelSubscription')
      ->once()
      ->andReturnUsing(
        function (string $id): DrSubscription {
          $updatedSubscription = $this->drSubscriptions[$id];
          $updatedSubscription->setState(DrSubscription::STATE_CANCELLED);
          return $updatedSubscription;
        }
      );
    return $this;
  }

  public function mockCreateFileLink(string $url = null): self
  {
    $fileLink = $this->drHelper->createFileLink($url);
    $this->drMock
      ->shouldReceive('createFileLink')
      ->once()
      ->andReturn($fileLink);
    return $this;
  }

  public function mockCreateRefund(): self
  {
    $this->drMock
      ->shouldReceive('createRefund')
      ->once()
      ->andReturnUsing(
        function (Refund $refund): DrOrderRefund {
          return $this->setDrRefund($this->drHelper->createOrderRefund($refund));
        }
      );
    return $this;
  }

  /**
   * the following are DR event sending helper
   */
  public function sendOrderAccepted(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.accepted', $drOrder, $eventId)
    );
  }

  public function sendOrderBlocked(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.blocked', $drOrder, $eventId)
    );
  }

  public function sendOrderCancelled(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.cancelled', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeFailed(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.failed', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeCaptureComplete(DrCharge $drCharge, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.capture.complete', $drCharge, $eventId)
    );
  }

  public function sendOrderChargeCaptureFailed(DrCharge $drCharge, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.capture.failed', $drCharge, $eventId)
    );
  }

  public function sendOrderComplete(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.complete', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeback(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.chargeback', $drOrder, $eventId)
    );
  }

  public function sendSubscriptionExtended(DrSubscription $drSubscription, DrInvoice $drInvoice, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.extended', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendSubscriptionFailed(DrSubscription $drSubscripiton, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.failed', $drSubscripiton, $eventId)
    );
  }

  public function sendSubscriptionPaymentFailed(DrSubscription $drSubscription, DrInvoice $drInvoice, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.payment_failed', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendSubscriptionReminder(DrSubscription $drSubscription, DrInvoice $drInvoice = null, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.reminder', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendOrderInvoiceCreated(DrOrder|string $drOrder, string $eventId = null)
  {
    $orderId = $drOrder instanceof DrOrder ? $drOrder->getId() : $drOrder;
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent(
        'order.invoice.created',
        ['orderId' => $orderId, 'fileId' => $this->drHelper->uuid()],
        $eventId
      )
    );
  }

  public function sendOrderCreditMemoCreated(DrOrder|string $drOrder, string $eventId = null)
  {
    $orderId = $drOrder instanceof DrOrder ? $drOrder->getId() : $drOrder;
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent(
        'order.credit_memo.created',
        ['orderId' => $orderId, 'fileId' => $this->drHelper->uuid()],
        $eventId
      )
    );
  }

  public function sendOrderRefunded(DrOrder $drOrder, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.refunded', $drOrder, $eventId)
    );
  }

  public function sendRefundPending(DrOrderRefund $orderRefund, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('refund.pending', $orderRefund, $eventId)
    );
  }

  public function sendRefundFailed(DrOrderRefund $orderRefund, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('refund.failed', $orderRefund, $eventId)
    );
  }

  public function sendRefundComplete(DrOrderRefund $orderRefund, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('refund.complete', $orderRefund, $eventId)
    );
  }


  /**
   * the followinig are reusable simple test cases
   */
  public function createOrUpdateBillingInfo(array $data = null)
  {
    // prepare
    $data = $data ?? [
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
    $response = $this->postJson('/api/v1/account/billing-info', $data);

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $response->assertSuccessful()->assertJson($data);
    $this->assertTrue(isset($this->user->dr['customer_id']));

    return $response;
  }

  public function createOrUpdatePaymentMethod(array $data = null)
  {
    // prepare
    $data = $data ?? [
      'type' => 'creditCard',
      'dr' => ['source_id' => 'digital-river-source-id-master'],
    ];

    // mock up
    $this->mockAttachCustomerSource();
    if ($this->user->payment_method->dr['source_id'] ?? null) {
      $this->mockDetachCustomerSource();
    }
    if ($activeSubscripiton = $this->user->getActiveLiveSubscription()) {
      $this->mockUpdateSubscriptionSource($activeSubscripiton);
    }

    $response = $this->postJson('/api/v1/account/payment-method',  $data);

    // refresh authenticated user data
    $this->user->refresh();

    // assert 
    $response->assertSuccessful();
    $this->assertEquals($this->user->payment_method->dr['source_id'], $data['dr']['source_id']);
    $this->assertEquals($this->user->payment_method->type, $data['type']);

    return $response;
  }

  public function createSubscription(array $data = null)
  {
    // prepare 
    $data = $data ?? ['plan_id' => Plan::public()->first()->id];

    // mock up
    $this->mockCreateCheckout();

    // call api
    $response = $this->postJson('/api/v1/account/subscriptions', $data);

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $response->assertSuccessful();
    $subscription = $this->user->getDraftSubscriptionById($response->json('id'));
    $this->assertNotNull($subscription);
    $this->assertEquals($subscription->status, Subscription::STATUS_DRAFT);

    $invoice = $subscription->getActiveInvoice();
    $this->assertEquals($invoice->status, Invoice::STATUS_INIT);

    return $response;
  }

  public function deleteSubscription(Subscription|int $subscription)
  {
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    // mock up
    if (isset($subscription->dr['checkout_id'])) {
      $this->mockDeleteCheckout();
    }
    if (isset($subscription->dr['subscription_id'])) {
      $this->mockDeleteSubscription();
    }

    // call api
    $response = $this->deleteJson("/api/v1/account/subscriptions/$id");

    // refresh authenticated user data
    $this->user->refresh();

    // assert
    $response->assertSuccessful();
    $subscription = $this->user->getDraftSubscriptionById($id);
    $this->assertNull($subscription);

    return $response;
  }

  public function paySubscription(Subscription|int $subscription, string $terms = 'this is test terms ...', string $orderState = DrOrder::STATE_ACCEPTED)
  {
    // prepare
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    // mock up
    $this->mockAttachCheckoutSource();
    $this->mockUpdateCheckoutTerms($subscription);
    $this->mockConvertCheckoutToOrder($subscription, $orderState);

    // call api
    $response = $this->postJson(
      "/api/v1/account/subscriptions/$id/pay",
      ['terms' => $terms]
    );

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_PENDING);
    $this->assertEquals(
      $subscription->sub_status,
      ($orderState == DrOrder::STATE_ACCEPTED) ? Subscription::SUB_STATUS_NORMAL : Subscription::SUB_STATUS_ORDER_PENDING
    );

    $invoice = $subscription->getActiveInvoice();
    $this->assertEquals($invoice->status, Invoice::STATUS_PENDING);

    return $response;
  }

  public function cancelSubscription(Subscription|int $subscription, bool $needRefund = false)
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $activeInvoice = $subscription->getActiveInvoice();
    $currentPeriodInvoice = $subscription->getCurrentPeriodInvoice();
    $currentPeriodInvoiceStatus = $currentPeriodInvoice->status;

    $this->assertNotEquals($subscription->sub_status, Subscription::SUB_STATUS_CANCELLING);

    // mock up
    $this->mockCancelSubscription($subscription);
    if ($needRefund && $currentPeriodInvoiceStatus == Invoice::STATUS_COMPLETED) {
      $this->mockCreateRefund();
    }
    Notification::fake();

    // call api
    $response = $this->postJson("/api/v1/account/subscriptions/{$subscription->id}/cancel", [
      'refund' => $needRefund,
    ]);

    // refresh authenticated user data
    $subscription->refresh();
    $activeInvoice?->refresh();
    $currentPeriodInvoice->refresh();

    // assert
    $response->assertSuccessful();
    if ($activeInvoice && $activeInvoice->id != $currentPeriodInvoice->id) {
      $this->assertEquals($activeInvoice->status, Invoice::STATUS_CANCELLED);
    }
    if ($needRefund) {
      $this->assertEquals($subscription->status, Subscription::STATUS_STOPPED);

      if ($currentPeriodInvoiceStatus == Invoice::STATUS_PROCESSING) {
        $this->assertEquals($currentPeriodInvoice->sub_status, Invoice::SUB_STATUS_TO_REFUND);
      } else {
        $this->assertEquals($currentPeriodInvoice->status, Invoice::STATUS_REFUNDING);
      }

      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED_REFUND
      );
    } else {
      $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_CANCELLING);

      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
      );
    }

    return $response;
  }

  public function cancelOrder(Subscription|int $subscription)
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = $subscription->getActiveInvoice();
    $tryCancel = $subscription->status == Subscription::STATUS_PENDING;

    // mock up
    if ($tryCancel) {
      $this->mockFulfillOrder();
    }
    Notification::fake();

    // call api
    $response = $this->postJson("/api/v1/account/invoices/{$invoice->id}/cancel");

    // refresh authenticated user data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    if ($tryCancel) {
      $response->assertSuccessful();
      $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);
      $this->assertEquals($invoice->status, Invoice::STATUS_CANCELLED);

      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_CANCELLED
      );
    } else {
      $response->assertStatus(409);
    }

    return $response;
  }

  public function createRefund(Invoice|int $invoice, float $amount = 0, string $reason = null)
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;

    // mock up
    $this->mockCreateRefund();

    // call api
    $response = $this->postJson("/api/v1/refunds", [
      'invoice_id' => $invoice->id,
      'amount' => $amount,
      'reason' => $reason,
    ]);

    // refresh authenticated user data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDING);
    $this->assertEquals($invoice->refunds()->where('status', Refund::STATUS_PENDING)->count(), 1);

    return $response;
  }

  public function onOrderAccept(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = $subscription->getActiveInvoice();

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_PENDING);

    // mock up
    $this->mockFulfillOrder();
    $this->mockActivateSubscription($subscription);
    if ($previousSubscription = $subscription->user->getActiveLiveSubscription()) {
      $this->mockCancelSubscription($previousSubscription);
    }
    Notification::fake();


    // call api
    $response = $this->sendOrderAccepted(
      $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_ACCEPTED)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($invoice->status, Invoice::STATUS_PROCESSING);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_CONFIRMED
    );
    if ($previousSubscription) {
      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
      );
    }

    return $subscription;
  }

  public function onOrderComplete(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoiceBeforeSubStatus = $invoice->sub_status;

    // prepare
    $this->assertContains($invoice->status, [Invoice::STATUS_PROCESSING]);

    // mock up
    if ($invoice->sub_status == Invoice::SUB_STATUS_TO_REFUND) {
      $this->mockCreateRefund();
    }

    // call api
    $response = $this->sendOrderComplete(
      $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_COMPLETE)
    );

    // refresh data
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertNull($subscription->active_invoice_id);
    if ($invoiceBeforeSubStatus == Invoice::SUB_STATUS_TO_REFUND) {
      $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDING);
    } else {
      $this->assertEquals($invoice->status, Invoice::STATUS_COMPLETED);
    }

    return $subscription;
  }

  private function onOrderFailed(Subscription $subscription, string $type): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = $subscription->getActiveInvoice();

    // prepare
    $this->assertContains($subscription->status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_PENDING]);
    $this->assertContains($invoice->status, [Invoice::STATUS_PROCESSING, Invoice::STATUS_PENDING]);


    // mock up
    Notification::fake();
    if ($subscription->status == Subscription::STATUS_ACTIVE) {
      $this->mockCancelSubscription($subscription);
    }

    // call api
    if ($type == 'order.blocked') {
      $order = $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_BLOCKED);
      $response = $this->sendOrderBlocked($order);
    } else if ($type == 'order.cancelled') {
      $order = $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderCancelled($order);
    } else if ($type == 'order.charge.failed') {
      $order = $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderChargeFailed($order);
    } else if ($type == 'order.charge.capture.failed') {
      $order = $this->drHelper->createCharge($subscription->dr['order_id'], DrCharge::STATE_FAILED);
      $response = $this->sendOrderChargeCaptureFailed($order);
    }

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);
    $this->assertEquals($invoice->status, Invoice::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_ABORTED
    );

    return $subscription;
  }

  public function onOrderBlocked(Subscription|int $subscription): Subscription
  {
    return $this->onOrderFailed($subscription, 'order.blocked');
  }

  public function onOrderCancelled(Subscription|int $subscription): Subscription
  {
    return $this->onOrderFailed($subscription, 'order.cancelled');
  }

  public function onOrderChargeFailed(Subscription|int $subscription): Subscription
  {
    return $this->onOrderFailed($subscription, 'order.charge.failed');
  }

  public function onOrderChargeCaptureFailed(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = Invoice::findByDrOrderId($subscription->getDrOrderId());

    // prepare
    $this->assertContains($invoice->status, [Invoice::STATUS_PROCESSING]);

    // mock up
    $this->mockGetOrder();
    if ($subscription->status == Subscription::STATUS_ACTIVE) {
      $this->mockCancelSubscription();
    }

    Notification::fake();

    // call api
    $order = $this->drHelper->createCharge($subscription->dr['order_id'], DrCharge::STATE_FAILED);
    $response = $this->sendOrderChargeCaptureFailed($order);

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);
    $this->assertEquals($invoice->status, Invoice::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_ABORTED
    );

    return $subscription;
  }

  public function onOrderInvoiceCreated(Invoice|int $invoice): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;

    // prepare
    $this->assertContains($invoice->status, [
      Invoice::STATUS_PROCESSING,
      Invoice::STATUS_COMPLETED,
      Invoice::STATUS_REFUNDED,
      Invoice::STATUS_REFUND_FAILED,
      Invoice::STATUS_REFUNDING,
      Invoice::STATUS_PARTLY_REFUNDED
    ]);

    // mock up
    $this->mockCreateFileLink();
    Notification::fake();

    // call api
    $response = $this->sendOrderInvoiceCreated(
      $this->drOrders[$invoice->getDrOrderId()]->setState(DrOrder::STATE_COMPLETE)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertNotNull($invoice->pdf_file);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_INVOICE
    );

    return $invoice;
  }

  public function onOrderCreditMemoCreated(Invoice|int $invoice): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;

    // prepare
    $this->assertContains($invoice->status, [
      Invoice::STATUS_COMPLETED,
      Invoice::STATUS_REFUNDED,
      Invoice::STATUS_REFUND_FAILED,
      Invoice::STATUS_REFUNDING,
      Invoice::STATUS_PARTLY_REFUNDED
    ]);

    // mock up
    $this->mockCreateFileLink();
    Notification::fake();

    // call api
    $response = $this->sendOrderCreditMemoCreated(
      $this->drOrders[$invoice->getDrOrderId()]->setState(DrOrder::STATE_COMPLETE)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertNotNull($invoice->credit_memos);
    $this->assertNotNull($invoice->credit_memos[0]['url']);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO
    );

    return $invoice;
  }

  public function onOrderRefunded(Invoice|int $invoice, float $totalAmount = 0): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;

    if ($totalAmount <= 0 || $totalAmount > $invoice->total_amount) {
      $totalAmount = $invoice->total_amount;
    }

    // prepare
    $this->assertContains($invoice->status, [
      Invoice::STATUS_COMPLETED,
      Invoice::STATUS_REFUND_FAILED,
      Invoice::STATUS_REFUNDING,
      Invoice::STATUS_PARTLY_REFUNDED
    ]);

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendOrderRefunded(
      $this->getDrOrder($invoice->getDrOrderId())
        ->setRefundedAmount($totalAmount)
        ->setAvailableToRefundAmount($invoice->total_amount - $totalAmount)
        ->setState(DrOrder::STATE_COMPLETE)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertNotNull($invoice->status == Invoice::STATUS_REFUNDED || $invoice->status == Invoice::STATUS_PARTLY_REFUNDED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_REFUNDED
    );

    return $invoice;
  }

  public function onRefundFailed(Invoice|int $invoice): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;
    $refund = $invoice->getActiveRefund();

    // prepare
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDING);

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendRefundFailed(
      $this->getDrRefund($refund->getDrRefundId())->setState(DrOrderRefund::STATE_FAILED)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();
    $refund->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUND_FAILED);
    $this->assertEquals($refund->status, Refund::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED
    );

    return $invoice;
  }

  public function onRefundComplete(Invoice|int $invoice): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof Invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;
    $refund = $invoice->getActiveRefund();

    // prepare
    $this->assertContains($refund->status, [
      Invoice::STATUS_PENDING,
    ]);

    // call api
    $response = $this->sendRefundComplete(
      $this->getDrRefund($refund->getDrRefundId())->setState(DrOrderRefund::STATE_SUCCEEDED)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();
    $refund->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($refund->status, Refund::STATUS_COMPLETED);

    return $invoice;
  }

  public function onSubscriptionReminder(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionReminder($this->drSubscriptions[$subscription->getDrSubscriptionId()]);

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_NORMAL);
    $this->assertNull($subscription->getActiveInvoice());

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_REMINDER
    );

    return $subscription;
  }

  public function onSubscriptionPaymentFailed(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $invoice = $subscription->getActiveInvoice();
    $invoiceStatus = $invoice?->status;

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);

    // mockup
    Notification::fake();

    // call api
    $drInvoice = $this->drInvoices[$invoice?->getDrInvoiceId()] ?? $this->drHelper->createInvoice($subscription);
    $this->drInvoices[$drInvoice->getId()] = $drInvoice;
    $response = $this->sendSubscriptionPaymentFailed(
      $this->drSubscriptions[$subscription->getDrSubscriptionId()],
      $drInvoice
    );

    // refresh data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->getActiveInvoice()->status, Invoice::STATUS_PENDING);

    if ($invoiceStatus == Invoice::STATUS_OPEN) {
      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_INVOICE_PENDING
      );
    }
    return $subscription;
  }

  public function onSubscriptionExtended(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $invoice = $subscription->getActiveInvoice();

    // mock up
    $this->mockUpdateOrderUpstreamId();
    Notification::fake();

    // call api
    $drOrder = $this->drHelper->createOrder($subscription, null, DrOrder::STATE_COMPLETE);
    $this->drOrders[$drOrder->getId()] = $drOrder;
    $drInvoice = $this->drInvoices[$invoice?->getDrInvoiceId()] ?? $this->drHelper->createInvoice($subscription, null, $drOrder->getId());
    $drInvoice->setOrderId($drOrder->getId());
    $this->drInvoices[$drInvoice->getId()] = $drInvoice;
    $response = $this->sendSubscriptionExtended(
      $this->drSubscriptions[$subscription->getDrSubscriptionId()],
      $drInvoice
    );

    // refresh data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_NORMAL);
    $this->assertEquals($invoice->status, Invoice::STATUS_COMPLETED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_EXTENDED
    );

    return $subscription;
  }

  public function onSubscriptionFailed(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $invoice = $subscription->getActiveInvoice();

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionFailed($this->drSubscriptions[$subscription->getDrSubscriptionId()]);

    // refresh data
    $subscription->refresh();
    $invoice?->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);
    $this->assertEquals($subscription->user->getActiveSubscription()->subscription_level, 1);
    if ($invoice) {
      $this->assertTrue($invoice->status == Invoice::STATUS_FAILED || $invoice->status == Invoice::STATUS_COMPLETED);
    }


    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_FAILED
    );

    return $subscription;
  }

  public function onOrderChargeback(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $previousStatus = $subscription->status;
    $previousSubStatus = $subscription->sub_status;

    // prepare
    $this->assertNotNull($subscription);

    // mock up
    if (
      $subscription->status == Subscription::STATUS_ACTIVE &&
      $subscription->sub_status != Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->mockCancelSubscription($subscription);
    }
    Notification::fake();

    // call api
    $response = $this->sendOrderChargeback(
      $this->drOrders[$subscription->getDrOrderId()]->setState(DrOrder::STATE_BLOCKED)
    );

    // refresh data
    $subscription->refresh();
    $this->user->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($this->user->type == User::TYPE_BLACKLISTED);
    $this->assertTrue(
      $subscription->status == Subscription::STATUS_ACTIVE && $subscription->sub_status == Subscription::SUB_STATUS_CANCELLING ||
        $subscription->status != Subscription::STATUS_ACTIVE
    );

    if ($previousStatus == Subscription::STATUS_ACTIVE && $previousSubStatus != Subscription::SUB_STATUS_CANCELLING) {
      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
      );
    }

    return $subscription;
  }
}
