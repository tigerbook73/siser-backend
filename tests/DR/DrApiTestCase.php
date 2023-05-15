<?php

namespace Tests\DR;

use App\Models\Base\BillingInfo;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\DigitalRiverService;
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
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\ApiTestCase;
use Tests\DR\DrTestTrait;
use Tests\Models\Subscription as ModelsSubscription;

class DrApiTestCase extends ApiTestCase
{
  public DrTestHelper $drHelper;
  public MockInterface $drMock;

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
  }

  /**
   * The followings are DR API mock helper
   */
  public function mockGetCustomer(DrCustomer $customer = null): self
  {
    $this->drMock
      ->shouldReceive('getCustomer')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $customer ?? $this->drHelper->createCustomer(id: $id)
      );
    return $this;
  }

  public function mockCreateCustomer(DrCustomer $customer = null): self
  {
    $this->drMock
      ->shouldReceive('createCustomer')
      ->once()
      ->andReturnUsing(
        fn (BillingInfo $billingInfo) =>
        $customer ?? $this->drHelper->createCustomer(billingInfo: $billingInfo)
      );
    return $this;
  }

  public function mockUpdateCustomer(DrCustomer $customer = null): self
  {
    $this->drMock
      ->shouldReceive('updateCustomer')
      ->once()
      ->andReturnUsing(
        fn (string $id, BillingInfo $billingInfo) =>
        $customer ?? $this->drHelper->createCustomer(id: $id, billingInfo: $billingInfo)
      );
    return $this;
  }

  public function mockAttachCustomerSource(DrSource $source = null): self
  {
    $this->drMock
      ->shouldReceive('attachCustomerSource')
      ->once()
      ->andReturnUsing(
        fn (string $customerId, string $sourceId) =>
        $source ?? $this->drHelper->createSource(id: $sourceId, customerId: $customerId)
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

  public function mockDetachCustomerSourceAsync(bool $result = true): self
  {
    $this->drMock
      ->shouldReceive('detachCustomerSourceAsync')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockGetCheckout(DrCheckout|Subscription $object): self
  {
    $checkout = $object instanceof DrCheckout ? $object : null;
    $subscription = $object instanceof Subscription ? $object : null;

    $this->drMock
      ->shouldReceive('getCheckout')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $checkout ?? $this->drHelper->createCheckout($subscription, $id)
      );

    return $this;
  }

  public function mockCreateCheckout(DrCheckout $checkout = null): self
  {
    $this->drMock
      ->shouldReceive('createCheckout')
      ->once()
      ->andReturnUsing(
        fn (Subscription $subscription) =>
        $checkout ?? $this->drHelper->createCheckout($subscription, null)
      );
    return $this;
  }

  public function mockUpdateCheckoutTerms(DrCheckout|Subscription $object): self
  {
    $checkout = $object instanceof DrCheckout ? $object : null;
    $subscription = $object instanceof Subscription ? $object : null;

    $this->drMock
      ->shouldReceive('updateCheckoutTerms')
      ->once()
      ->andReturnUsing(
        fn (string $checkoutId, string $terms) =>
        $checkout ?? $this->drHelper->createCheckout($subscription, $checkoutId)
      );
    return $this;
  }

  public function mockDeleteCheckout(bool $result = true): self
  {
    $this->drMock
      ->shouldReceive('deleteCheckout')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockDeleteCheckoutAsync(bool $result = true): self
  {
    $this->drMock
      ->shouldReceive('deleteCheckoutAsync')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockAttachCheckoutSource(DrSource $source = null): self
  {
    $this->drMock
      ->shouldReceive('attachCheckoutSource')
      ->once()
      ->andReturnUsing(
        fn (string $checkoutId, string $sourceId) =>
        $source ?? $this->drHelper->createSource($sourceId)
      );
    return $this;
  }

  public function mockGetSource(DrSource $source = null): self
  {
    $this->drMock
      ->shouldReceive('getSource')
      ->once()
      ->andReturnUsing(
        fn (string $sourceId) =>
        $source ?? $this->drHelper->createSource($sourceId)
      );
    return $this;
  }

  public function mockGetOrder(DrOrder|Subscription $object, string $state = null): self
  {
    $order = ($object instanceof DrOrder) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('getOrder')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $order ?? $this->drHelper->createOrder($subscription, $id, $state)
      );
    return $this;
  }

  public function mockConvertCheckoutToOrder(DrOrder|Subscription $object, string $state = null): self
  {
    $order = ($object instanceof DrOrder) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('convertCheckoutToOrder')
      ->once()
      ->andReturnUsing(
        fn (string $checkoutId) =>
        $order ?? $this->drHelper->createOrder($subscription, null, $state)
      );

    return $this;
  }

  public function mockFulfillOrder(DrFulfillment $fulfillment = null): self
  {
    $this->drMock
      ->shouldReceive('fulfillOrder')
      ->once()
      ->andReturnUsing(
        fn (string $orderId, DrOrder $order = null, bool $cancel = false) =>
        $fulfillment ?? $this->drHelper->createFulfillment()
      );

    return $this;
  }

  public function mockGetSubscription(DrSubscription|Subscription $object, string $id = null, bool $next = false): self
  {
    $drSubscription = ($object instanceof DrSubscription) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('getSubscription')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $drSubscription ?? $this->drHelper->createSubscription($subscription, $id, $next)
      );
    return $this;
  }

  public function mockActivateSubscription(DrSubscription|Subscription $object, string $id = null, bool $next = false): self
  {
    $drSubscription = ($object instanceof DrSubscription) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('activateSubscription')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $drSubscription ?? $this->drHelper->createSubscription($subscription, $id, $next)
      );
    return $this;
  }

  public function mockDeleteSubscription(bool $result = false): self
  {
    $this->drMock
      ->shouldReceive('deleteSubscription')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockDeleteSubscriptionAsync(bool $result = false): self
  {
    $this->drMock
      ->shouldReceive('deleteSubscriptionAsync')
      ->once()
      ->andReturn($result);
    return $this;
  }

  public function mockUpdateSubscriptionSource(DrSubscription|Subscription $object, string $id = null, bool $next = false): self
  {
    $drSubscription = ($object instanceof DrSubscription) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('updateSubscriptionSource')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $drSubscription ?? $this->drHelper->createSubscription($subscription, $id, $next)
      );
    return $this;
  }

  public function mockUpdateSubscriptionItems(DrSubscription|Subscription $object, string $id = null, bool $next = false): self
  {
    $drSubscription = ($object instanceof DrSubscription) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('updateSubscriptionItems')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $drSubscription ?? $this->drHelper->createSubscription($subscription, $id, $next)
      );
    return $this;
  }

  public function mockCancelSubscription(DrSubscription|Subscription $object, string $id = null, bool $next = false): self
  {
    $drSubscription = ($object instanceof DrSubscription) ? $object : null;
    $subscription = ($object instanceof Subscription) ? $object : null;

    $this->drMock
      ->shouldReceive('cancelSubscription')
      ->once()
      ->andReturnUsing(
        fn (string $id) =>
        $drSubscription ?? $this->drHelper->createSubscription($subscription, $id, $next)
      );
    return $this;
  }

  public function mockCreateFileLink(DrFileLink|string $url = null): self
  {
    $fileLink = ($url instanceof DrFileLink) ? $url
      : $this->drHelper->createFileLink($url);

    $this->drMock
      ->shouldReceive('createFileLink')
      ->once()
      ->andReturn($fileLink);
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

  public function sendSubscriptionPaymentFailed(DrSubscription $drSubscription, DrInvoice $drInvoice = null, string $eventId = null)
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

  public function sendInvoiceOpen(DrInvoice $drInvoice, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('invoice.open', $drInvoice, $eventId)
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
      $this->mockDetachCustomerSourceAsync();
    }
    if ($activeSubscripiton = $this->user->getActiveLiveSubscription()) {
      $this->mockUpdateSubscriptionSource($activeSubscripiton);
    }

    $response = $this->postJson('/api/v1/account/payment-method',  $data);

    // refresh authenticated user data
    $this->user->refresh();

    // assert 
    $response->assertSuccessful();
    $this->assertTrue($this->user->payment_method->dr['source_id'] == $data['dr']['source_id']);
    $this->assertTrue($this->user->payment_method->type == $data['type']);

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
    $this->assertTrue(!!$subscription);
    $this->assertTrue($subscription->status == 'draft');

    return $response;
  }

  public function deleteSubscription(Subscription|int $subscription)
  {
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

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

  public function paySubscription(Subscription|int $subscription, string $terms = 'this is test terms ...')
  {
    // prepare
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    // mock up
    $this->mockAttachCheckoutSource();
    $this->mockUpdateCheckoutTerms($subscription);
    $this->mockConvertCheckoutToOrder($subscription);

    // call api
    $response = $this->postJson(
      "/api/v1/account/subscriptions/$id/pay",
      ['terms' => $terms]
    );

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'pending');

    return $response;
  }

  public function cancelSubscription(Subscription|int $subscription)
  {
    // prepare
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    $invoice = $subscription->getActiveInvoice();
    $this->assertTrue($subscription->sub_status != 'cancelling');

    // mock up
    $this->mockCancelSubscription($subscription);
    Notification::fake();

    // call api
    $response = $this->postJson("/api/v1/account/subscriptions/{$id}/cancel");

    // refresh authenticated user data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->sub_status == 'cancelling');
    $this->assertTrue(!$invoice || $invoice->status == 'void' || $invoice->status == 'completing');

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
    );

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
    Notification::fake();

    // call api
    $response = $this->sendOrderComplete($this->drHelper->createOrder(
      $subscription,
      null,
      DrOrder::STATE_COMPLETE
    ));

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active');
    $this->assertTrue($subscription->getActiveInvoice()->status == 'completing');

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CONFIRMED
    );

    return $subscription;
  }

  private function onOrderFailed(Subscription $subscription, string $type)
  {
    // prepare
    $this->assertTrue($subscription->status == 'processing' || $subscription->status == 'pending');

    // mock up
    Notification::fake();

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

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ABORTED
    );

    return $subscription;
  }

  public function onOrderBlocked(Subscription $subscription)
  {
    return $this->onOrderFailed($subscription, 'order.blocked');
  }

  public function onOrderCancelled(Subscription $subscription)
  {
    return $this->onOrderFailed($subscription, 'order.cancelled');
  }

  public function onOrderChargeFailed(Subscription $subscription)
  {
    return $this->onOrderFailed($subscription, 'order.charge.failed');
  }

  public function onOrderChargeCaptureFailed(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'processing' || $subscription->status == 'pending');

    // mock up
    $this->mockGetOrder($subscription);

    Notification::fake();

    // call api
    $order = $this->drHelper->createCharge($subscription->dr['order_id'], DrCharge::STATE_FAILED);
    $response = $this->sendOrderChargeCaptureFailed($order);

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'failed');

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ABORTED
    );

    return $subscription;
  }

  public function onOrderInvoiceCompleted(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');
    $invoice = $subscription->getActiveInvoice();

    // mock up
    $this->mockGetOrder($subscription);
    $this->mockCreateFileLink();
    Notification::fake();

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

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_INVOICE_PDF
    );

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

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($subscription->getActiveInvoice()->status == 'open');

    return $subscription;
  }

  public function onSubscriptionReminder(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionReminder($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($$subscription->getActiveInvoice() == null);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_REMINDER
    );

    return $subscription;
  }

  public function onSubscriptionPaymentFailed(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');

    // mockup
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionPaymentFailed($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'overdue');
    $this->assertTrue($subscription->getActiveInvoice()->status == 'overdue');

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_OVERDUE
    );

    return $subscription;
  }

  public function onSubscriptionExtended(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');
    $invoice = $subscription->getActiveInvoice();

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionExtended(
      $this->drHelper->createSubscription($subscription),
      $this->drHelper->createInvoice($subscription)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'active' || $subscription->sub_status = 'normal');
    $this->assertTrue($invoice->status == 'completing');

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_EXTENDED
    );

    return $subscription;
  }

  public function onSubscriptionFailed(Subscription $subscription)
  {
    // prepare
    $this->assertTrue($subscription->status == 'active');
    $invoice = $subscription->getActiveInvoice();

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionFailed($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == 'failed');
    $this->assertTrue($invoice->status == 'failed');
    $this->assertTrue($subscription->user->getActiveSubscription()->subscription_level == 1);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_FAILED
    );

    return $subscription;
  }
}
