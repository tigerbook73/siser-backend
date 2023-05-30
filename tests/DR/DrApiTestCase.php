<?php

namespace Tests\DR;

use App\Models\Base\BillingInfo;
use App\Models\Invoice;
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
    $this->assertNull($subscription);

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
    $this->assertEquals($subscription->status, Subscription::STATUS_PENDING);

    return $response;
  }

  public function cancelSubscription(Subscription|int $subscription)
  {
    // prepare
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    $invoice = $subscription->getActiveInvoice();
    $this->assertNotEquals($subscription->sub_status, Subscription::SUB_STATUS_CANCELLING);

    // mock up
    $this->mockCancelSubscription($subscription);
    Notification::fake();

    // call api
    $response = $this->postJson("/api/v1/account/subscriptions/{$id}/cancel");

    // refresh authenticated user data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : null;

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_CANCELLING);
    if ($invoice) {
      $this->assertTrue($invoice->status == Invoice::STATUS_VOID || $invoice->status == Invoice::STATUS_COMPLETING);
    }

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
    );

    return $response;
  }

  public function onOrderAccept(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_PENDING);

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
    $this->assertEquals($subscription->status, Subscription::STATUS_PROCESSING);

    return $subscription;
  }

  public function onOrderComplete(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_PROCESSING);

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
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->getActiveInvoice()->status, Invoice::STATUS_COMPLETING);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CONFIRMED
    );
    if ($previousSubscription) {
      Notification::assertSentTo(
        $subscription,
        fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_CANCELLED
      );
    }

    return $subscription;
  }

  private function onOrderFailed(Subscription $subscription, string $type): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertTrue($subscription->status == Subscription::STATUS_PROCESSING || $subscription->status == Subscription::STATUS_PENDING);

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
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ABORTED
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

    // prepare
    $this->assertTrue($subscription->status == Subscription::STATUS_PROCESSING || $subscription->status == Subscription::STATUS_PENDING);

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
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ABORTED
    );

    return $subscription;
  }

  public function onOrderInvoiceCompleted(Subscription|int $subscription): Subscription
  {
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
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
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertTrue($subscription->sub_status == Subscription::SUB_STATUS_NORMAL || $subscription->sub_status == Subscription::SUB_STATUS_CANCELLING);
    $this->assertEquals($invoice->status, Invoice::STATUS_COMPLETED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_INVOICE_PDF
    );

    return $subscription;
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
    $response = $this->sendSubscriptionReminder($this->drHelper->createSubscription($subscription));

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
    $response = $this->sendSubscriptionPaymentFailed(
      $this->drHelper->createSubscription($subscription),
      $this->drHelper->createInvoice($subscription, $invoice?->getDrInvoiceId() ?? $this->drHelper->uuid())
    );

    // refresh data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_INVOICE_PENDING);
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
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionExtended(
      $this->drHelper->createSubscription($subscription),
      $this->drHelper->createInvoice(
        $subscription,
        $invoice?->getDrInvoiceId() ?? $this->drHelper->uuid(),
        $this->drHelper->uuid()
      )
    );

    // refresh data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_INVOICE_COMPLETING);
    $this->assertEquals($invoice->status, Invoice::STATUS_COMPLETING);

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
    $response = $this->sendSubscriptionFailed($this->drHelper->createSubscription($subscription));

    // refresh data
    $subscription->refresh();
    $invoice?->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_FAILED);
    $this->assertEquals($subscription->user->getActiveSubscription()->subscription_level, 1);
    if ($invoice) {
      $this->assertTrue($invoice->status == Invoice::STATUS_FAILED);
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
    $response = $this->sendOrderChargeback($this->drHelper->createOrder(
      $subscription,
      null,
      DrOrder::STATE_COMPLETE
    ));

    // refresh data
    $subscription->refresh();
    $this->user->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($this->user->blacklisted);
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
