<?php

namespace Tests\DR;

use App\Models\BillingInfo;
use App\Models\Subscription;
use App\Services\DigitalRiver\DigitalRiverService;
use Closure;
use DigitalRiver\ApiSdk\Model\Charge as DrCharge;
use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\CreditCard as DrCreditCard;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\FileLink as DrFileLink;
use DigitalRiver\ApiSdk\Model\Fulfillment as DrFulfillment;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Source as DrSource;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
use Mockery\MockInterface;


trait DrTestTrait
{
  public DrTestHelper $drHelper;
  public MockInterface $drMock;

  /**
   * interface requirect 
   *
   * @param  string  $abstract
   * @param  \Closure|null  $mock
   * @return \Mockery\MockInterface
   */
  abstract protected function mock($abstract, Closure $mock = null);

  /**
   * This mothod will be called by setUp()
   */
  public function setupDrTestTrait()
  {
    $this->drHelper = new DrTestHelper();
    $this->drMock = $this->mock(
      DigitalRiverService::class
    );
  }

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

  public function mockUpdateCheckoutTerms(DrCheckout|Subscription $object = null): self
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

  public function mockConvertCheckoutToOrder(DrOrder|Subscription $object = null, string $state = null): self
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
        $order ?? $this->drHelper->createFulfillment()
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

  public function sendOrderAccepted(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.accepted', $drOrder, $eventId)
    );
  }

  public function sendOrderBlocked(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.blocked', $drOrder, $eventId)
    );
  }

  public function sendOrderCancelled(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.cancelled', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeFailed(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.failed', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeCaptureComplete(DrCharge $drCharge, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.capture.complete', $drCharge, $eventId)
    );
  }

  public function sendOrderChargeCaptureFailed(DrCharge $drCharge, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.charge.capture.failed', $drCharge, $eventId)
    );
  }

  public function sendOrderComplete(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.complete', $drOrder, $eventId)
    );
  }

  public function sendOrderChargeback(DrOrder $drOrder, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('order.chargeback', $drOrder, $eventId)
    );
  }

  public function sendSubscriptionExtended(DrSubscription $drSubscription, DrInvoice $drInvoice, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.extended', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendSubscriptionFailed(DrSubscription $drSubscripiton, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.failed', $drSubscripiton, $eventId)
    );
  }

  public function sendSubscriptionPaymentFailed(DrSubscription $drSubscription, DrInvoice $drInvoice = null, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.payment_failed', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendSubscriptionReminder(DrSubscription $drSubscription, DrInvoice $drInvoice = null, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('subscription.reminder', ['subscription' => $drSubscription, 'invoice' => $drInvoice], $eventId)
    );
  }

  public function sendInvoiceOpen(DrInvoice $drInvoice, string $eventId = null)
  {
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent('invoice.open', $drInvoice, $eventId)
    );
  }

  public function sendOrderInvoiceCreated(DrOrder|string $drOrder, string $eventId = null)
  {
    $orderId = $drOrder instanceof DrOrder ? $drOrder->getId() : $drOrder;
    $this->post(
      '/api/v1/dr/webhooks',
      $this->drHelper->createEvent(
        'order.invoice.created',
        ['orderId' => $orderId, 'fileId' => $this->drHelper->uuid()],
        $eventId
      )
    );
  }
}
