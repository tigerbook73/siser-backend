<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionNotification;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Exception;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionPendingTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_pending()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    return $this->paySubscription($response->json('id'));
  }

  public function test_pending_to_processing()
  {
    $response = $this->init_pending();

    return $this->onOrderAccept(Subscription::find($response->json('id')));
  }

  public function test_pending_to_processing_error_fulfill()
  {
    $response = $this->init_pending();

    /** @var Subscription $subscription */
    $subscription = Subscription::find($response->json('id'));

    // prepare
    $this->assertTrue($subscription->status == Subscription::STATUS_PENDING);

    // mock up
    $this->drMock
      ->shouldReceive('fulfillOrder')
      ->once()
      ->andThrow(new Exception('test', 444));

    Notification::fake();

    // call api
    $response = $this->sendOrderAccepted($this->drOrders[$subscription->getActiveInvoice()->getDrOrderId()]->setState(DrOrder::STATE_ACCEPTED));

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == Subscription::STATUS_FAILED);

    Notification::assertSentTo(
      $subscription,
      fn (SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_ORDER_ABORTED
    );

    return $subscription;
  }

  public function test_pending_to_failed_blocked()
  {
    $response = $this->init_pending();

    return $this->onOrderBlocked(Subscription::find($response->json('id')));
  }

  public function test_pending_to_failed_cancelled()
  {
    $response = $this->init_pending();

    return $this->onOrderCancelled(Subscription::find($response->json('id')));
  }

  public function test_pending_to_failed_cancelled_by_order()
  {
    $response = $this->init_pending();

    $response = $this->cancelOrder(Subscription::find($response->json('id')));
    $response->assertStatus(200);
  }

  public function test_pending_to_failed_charge_failed()
  {
    $response = $this->init_pending();

    return $this->onOrderChargeFailed(Subscription::find($response->json('id')));
  }

  public function test_pending_to_failed_capture_failed()
  {
    $response = $this->init_pending();

    return $this->onOrderChargeCaptureFailed(Subscription::find($response->json('id')));
  }

  public function test_pending_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_pending();

    Notification::fake();

    Carbon::setTestNow('2023-01-01 00:31:00');
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', Subscription::STATUS_PENDING)->count() > 0);

    Notification::assertSentTo(
      new Developer,
      fn (SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_pending_not_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_pending();

    Notification::fake();

    Carbon::setTestNow('2023-01-01 00:29:00');
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', Subscription::STATUS_PENDING)->count() > 0);

    Notification::assertNothingSent();
  }
}
