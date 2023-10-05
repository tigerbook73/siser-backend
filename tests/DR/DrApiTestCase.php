<?php

namespace Tests\DR;

use App\Models\Base\BillingInfo;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\DigitalRiverService;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CreditCard as DrCreditCard;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\CustomerTaxIdentifier as DrCustomerTaxIdentifier;
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
  public function mockGetCustomer(): self
  {
    $this->drMock
      ->shouldReceive('getCustomer')
      ->once()
      ->andReturnUsing(
        function (string $id): DrCustomer {
          return $this->drHelper->getDrCustomer($id);
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
          return $this->drHelper->createCustomer($billingInfo);
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
          return $this->drHelper->updateCustomer($id, $billingInfo);
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
          return $this->drHelper->attachCustomerSource($customerId, $sourceId);
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
          return $this->drHelper->getDrCheckout($id);
        }
      );

    return $this;
  }

  public function mockCreateTaxId(): self
  {
    $this->drMock
      ->shouldReceive('createTaxId')
      ->once()
      ->andReturnUsing(
        function (string $type, string $value): DrCustomerTaxIdentifier {
          return  $this->drHelper->createTaxId($type, $value);
        }
      );
    return $this;
  }

  public function mockDeleteTaxId(): self
  {
    $this->drMock
      ->shouldReceive('deleteTaxId')
      ->once()
      ->andReturnUsing(
        function (string $id): void {
          $this->drHelper->deleteTaxId($id);
        }
      );
    return $this;
  }

  public function mockAttachCustomerTaxId(): self
  {
    $this->drMock
      ->shouldReceive('attachCustomerTaxId')
      ->once()
      ->andReturnUsing(
        function (string $customerId, string $taxId) {
          return $this->drHelper->attachCustomerTaxId($customerId, $taxId);
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
          return  $this->drHelper->createCheckout($subscription);
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
          $updatedCheckout = $this->drHelper->getDrCheckout($checkoutId);
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
            $this->drHelper->deleteCheckout($checkoutId);
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
          return $this->drHelper->attachCheckoutSource($checkoutId, $sourceId);
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
          return $this->drHelper->getDrSource(id: $sourceId);
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
          return $this->drHelper->getDrOrder($id);
        }
      );
    return $this;
  }

  public function mockConvertCheckoutToOrder(string $state = DrOrder::STATE_ACCEPTED): self
  {
    $this->drMock
      ->shouldReceive('convertCheckoutToOrder')
      ->once()
      ->andReturnUsing(
        function (string $checkoutId) use ($state): DrOrder {
          $newOrder = $this->drHelper->convertChekcoutToOrder($checkoutId, $state);
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
          return $this->drHelper->updateOrderUpstreamId($orderId, $upstreamId);
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
          return $this->drHelper->fulfillOrder($orderId, $order, $cancel);
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
          return $this->drHelper->getDrSubscription($id);
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
          return $this->drHelper->activateSubscription($id);
        }
      );
    return $this;
  }

  public function mockConvertSubscriptionToNext(): self
  {
    $this->drMock
      ->shouldReceive('convertSubscriptionToNext')
      ->once()
      ->andReturnUsing(
        function (DrSubscription $drSubscription, Subscription $subscription): DrSubscription {
          return $drSubscription;
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
            $this->drHelper->deleteSubscription($id);
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
          $updatedSubscription = $this->drHelper->getDrSubscription($id);
          $updatedSubscription->setSourceId($sourceId);
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
          $updatedSubscription = $this->drHelper->getDrSubscription($id);
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
          return $this->drHelper->createOrderRefund($refund);
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

  public function sendSubscriptionFailed(DrSubscription $drSubscription, string $eventId = null)
  {
    return $this->postJson(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.failed', $drSubscription, $eventId)
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
    if ($this->user->getDrCustomerId()) {
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
    $this->assertNotNull($this->user->getDrCustomerId());

    return $response;
  }

  public function createOrUpdatePaymentMethod(array $data = null)
  {
    // prepare
    $data = $data ?? [
      'type' => 'creditCard',
      'dr' => ['source_id' => $this->drHelper->uuid()],
    ];

    // mock up
    $this->mockAttachCustomerSource();
    if ($this->user->payment_method?->getDrSourceId()) {
      $this->mockDetachCustomerSource();
    }
    if ($activeSubscription = $this->user->getActiveLiveSubscription()) {
      $this->mockUpdateSubscriptionSource($activeSubscription);
    }

    $response = $this->postJson('/api/v1/account/payment-method',  $data);

    // refresh authenticated user data
    $this->user->refresh();

    // assert 
    $response->assertSuccessful();
    $this->assertEquals($this->user->payment_method->getDrSourceId(), $data['dr']['source_id']);
    $this->assertEquals($this->user->payment_method->type, $data['type']);

    return $response;
  }

  public function createTaxId(string $type = 'au', string $value = 'ABN12345678901'): TaxId
  {
    // prepare
    $data = [
      'type' => $type,
      'value' => $value,
    ];

    // mock up
    $this->mockCreateTaxId();
    $this->mockAttachCustomerTaxId();

    $response = $this->postJson('/api/v1/account/tax-ids', $data);

    // refresh authenticated user data
    $taxId = $this->user->tax_ids()->where('type', $type)->where('value', $value)->first();

    // assert 
    $response->assertSuccessful();
    $this->assertNotNull($taxId);

    return $taxId;
  }

  public function retrieveTaxRate(string $taxId = null)
  {
    // prepare
    $data = $taxId ? ['tax_id' => $taxId] : [];

    // mock up
    $this->mockCreateCheckout();

    $response = $this->postJson('/api/v1/account/tax-rate', $data);

    // assert 
    $response->assertSuccessful();
    $response->assertJson($taxId ? ['tax_rate' => 0] : ['tax_rate' => $this->drHelper->getTaxRate()]);
    return $response;
  }

  public function createSubscription($planInterval = Plan::INTERVAL_MONTH, string|null $couponType = null, string $taxId = null)
  {
    /** @var Plan $plan */
    $plan = Plan::public()->where('interval', $planInterval)->first();

    /** @var Coupon|null $coupon */
    $coupon = null;
    if ($couponType == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      $coupon = Coupon::where('discount_type', Coupon::DISCOUNT_TYPE_FREE_TRIAL)
        ->where('interval', Coupon::INTERVAL_MONTH)
        ->where('interval_count', 3)
        ->first();
    } elseif ($couponType == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
      $coupon = Coupon::where('discount_type', Coupon::DISCOUNT_TYPE_PERCENTAGE)
        ->where('interval', $plan->interval)
        ->where('inverval_count', 3)
        ->first();
    }

    // prepare 
    $data = ['plan_id' => $plan->id];
    if ($coupon) {
      $data['coupon_id'] = $coupon->id;
    }
    if ($taxId) {
      $data['tax_id'] = $taxId;
    }


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
    /** @var Subscription $subscription */
    $subscription = ($subscription instanceof Subscription) ? $subscription : Subscription::find($subscription);
    $id = $subscription->id;

    // mock up
    if ($subscription->getDrCheckoutId()) {
      $this->mockDeleteCheckout();
    }
    if ($subscription->getDrSubscriptionId()) {
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
    $this->mockConvertCheckoutToOrder($orderState);

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
    if (
      $subscription->plan_info['interval'] == Plan::INTERVAL_YEAR ||
      ($subscription->coupon_info['discount_type'] ?? null) == Coupon::DISCOUNT_TYPE_FREE_TRIAL ||
      (($subscription->coupon_info['discount_type'] ?? null) == Coupon::DISCOUNT_TYPE_PERCENTAGE &&
        ($subscription->coupon_info['interval_count'] ?? null) == $subscription->plan_info['interval_count'])
    ) {
      $this->mockConvertSubscriptionToNext($subscription);
    }
    Notification::fake();


    // call api
    $response = $this->sendOrderAccepted(
      $this->drHelper->getDrOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_ACCEPTED)
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($invoice->status, Invoice::STATUS_PROCESSING);

    // free trial
    if ($subscription->isFreeTrial()) {
      $this->assertLessThan(0.004, abs($subscription->price - 0));
      $this->assertLessThan(0.004, abs($subscription->next_invoice['price'] - $subscription->plan_info['price']['price']));
      $this->assertNull($subscription->next_invoice['coupon_info']);
    } else if (
      $subscription->plan_info['interval'] == Plan::INTERVAL_YEAR
    ) {
      $this->assertNotEquals($subscription->plan_info['id'], $subscription->next_invoice['plan_info']['id']);
      $this->assertLessThan(0.004, abs($subscription->next_invoice['price'] - $subscription->next_invoice['plan_info']['price']['price']));
      $this->assertNull($subscription->next_invoice['coupon_info']);
    } else if (
      $subscription->isFixedTermPercentage() &&
      $subscription->current_period == $subscription->coupon_info['interval_count'] / $subscription->plan_info['interval_count']
    ) {
      $this->assertLessThan(0.004, abs($subscription->price - $subscription->plan_info['price']['price'] * $subscription->coupon_info['discount'] / 100));
      $this->assertLessThan(0.004, abs($subscription->next_invoice['price'] - $subscription->plan_info['price']['price']));
      $this->assertNull($subscription->next_invoice['coupon_info']);
    } else if (
      $subscription->isPercentage()
    ) {
      $this->assertLessThan(0.004, abs($subscription->price - $subscription->plan_info['price']['price'] * $subscription->coupon_info['discount'] / 100));
      $this->assertLessThan(0.004, abs($subscription->price - $subscription->next_invoice['price']));
      $this->assertNotNull($subscription->next_invoice['coupon_info']);
    } else {
      $this->assertLessThan(0.004, abs($subscription->price - $subscription->plan_info['price']['price']));
      $this->assertLessThan(0.004, abs($subscription->price - $subscription->next_invoice['price']));
    }

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
      $this->drHelper->getDrOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_COMPLETE)
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
      $order = $this->drHelper->getDrOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_BLOCKED);
      $response = $this->sendOrderBlocked($order);
    } else if ($type == 'order.cancelled') {
      $order = $this->drHelper->getDrOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderCancelled($order);
    } else if ($type == 'order.charge.failed') {
      $order = $this->drHelper->getDrOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_CANCELLED);
      $response = $this->sendOrderChargeFailed($order);
    } else if ($type == 'order.charge.capture.failed') {
      $charge = $this->drHelper->createCharge($subscription->getDrOrderId(), DrCharge::STATE_FAILED);
      $response = $this->sendOrderChargeCaptureFailed($charge);
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
    $order = $this->drHelper->createCharge($subscription->getDrOrderId(), DrCharge::STATE_FAILED);
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
      $this->drHelper->getDrOrder($invoice->getDrOrderId())->setState(DrOrder::STATE_COMPLETE)
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
      $this->drHelper->getDrOrder($invoice->getDrOrderId())->setState(DrOrder::STATE_COMPLETE)
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
      $this->drHelper->getDrOrder($invoice->getDrOrderId())
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

  public function onRefundPending(Invoice $invoice, bool $chargeBack = false): Refund
  {
    // prepare
    $drRefund = $chargeBack ?
      $this->drHelper->createChargeBackRefund($invoice) :
      $this->drHelper->getDrRefund($invoice->getActiveRefund()->getDrRefundId());

    // call api
    $response = $this->sendRefundPending($drRefund);

    // refresh data
    $invoice->refresh();
    $refund = $invoice->getActiveRefund();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDING);
    $this->assertEquals($refund->status, Refund::STATUS_PENDING);

    return $refund;
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
      $this->drHelper->getDrRefund($refund->getDrRefundId())->setState(DrOrderRefund::STATE_FAILED)
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
      $this->drHelper->getDrRefund($refund->getDrRefundId())->setState(DrOrderRefund::STATE_SUCCEEDED)
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

    // create next dr invoice
    $drSubscription = $this->drHelper->getDrSubscription($subscription->getDrSubscriptionId());
    $drInvoice = $this->drHelper->createInvoice($subscription);

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionReminder($drSubscription, $drInvoice);

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

    // create next dr invoice
    $drSubscription = $this->drHelper->getDrSubscription($subscription->getDrSubscriptionId());
    $drInvoice = $this->drHelper->getDrInvoice($invoice?->getDrInvoiceId()) ?? $this->drHelper->createInvoice($subscription);
    $drInvoice->setState(DrInvoice::STATE_OPEN);

    // mockup
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionPaymentFailed(
      $drSubscription,
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
    $invoice = $subscription->getActiveInvoice();

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);

    $drSubscription = $this->drHelper->getDrSubscription($subscription->getDrSubscriptionId());
    $drOrder = $this->drHelper->createOrderFromInvoice($invoice);
    $drInvoice = $this->drHelper->getDrInvoice($invoice->getDrInvoiceId());
    $drInvoice->setState(DrInvoice::STATE_PAID)
      ->setOrderId($drOrder->getId());

    // mock up
    $this->mockUpdateOrderUpstreamId();
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionExtended(
      $drSubscription,
      $drInvoice
    );

    // refresh data
    $subscription->refresh();
    $invoice = $invoice ? $invoice->refresh() : $subscription->getActiveInvoice();

    // assert
    $response->assertSuccessful();
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $this->assertEquals($subscription->sub_status, Subscription::SUB_STATUS_NORMAL);
    $this->assertNull($subscription->getActiveInvoice());
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
    $invoice = $subscription->getActiveInvoice();

    // prepare
    $this->assertEquals($subscription->status, Subscription::STATUS_ACTIVE);
    $drSubscription = $this->drHelper->getDrSubscription($subscription->getDrSubscriptionId());
    $drInvoice = $this->drHelper->getDrInvoice($invoice?->getDrInvoiceId()) ?? $this->drHelper->createInvoice($subscription);
    $drInvoice->setState(DrInvoice::STATE_UNCOLLECTIBLE);

    // mock up
    Notification::fake();

    // call api
    $response = $this->sendSubscriptionFailed($drSubscription);

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

  public function onOrderChargeback(Invoice|int $invoice): Invoice
  {
    /** @var Invoice $invoice */
    $invoice = ($invoice instanceof invoice) ? $invoice : Invoice::find($invoice);
    $subscription = $invoice->subscription;
    $previousStatus = $subscription->status;
    $previousSubStatus = $subscription->sub_status;

    // prepare
    $this->assertEquals(Invoice::STATUS_COMPLETED, $invoice->status);
    $drOrder = $invoice->getDrInvoiceId() ?
      $this->drHelper->createInvoiceOrder($this->drHelper->getDrInvoice($invoice->getDrInvoiceId())) :
      $this->drHelper->getDrOrder($invoice->getDrOrderId());

    // mock up
    if (
      $subscription->status == Subscription::STATUS_ACTIVE &&
      $subscription->sub_status != Subscription::SUB_STATUS_CANCELLING
    ) {
      $this->mockCancelSubscription($subscription);
    }
    Notification::fake();

    // call api
    $response = $this->sendOrderChargeback($drOrder);

    // refresh data
    $subscription->refresh();
    $invoice->refresh();
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

    return $invoice;
  }
}
