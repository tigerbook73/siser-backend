<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Exception;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionProcessingTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_processing()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    return $this->onOrderAccept(Subscription::find($response->json('id')));
  }

  public function test_processing_to_active_invoice_completed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderComplete($subscription);
  }

  public function test_processing_to_active_invoice_completed_error()
  {
    $subscription = $this->init_processing();

    // mock up
    $this->drMock
      ->shouldReceive('activateSubscription')
      ->once()
      ->andThrow(new Exception('test', 444));
    Notification::fake();

    // call api
    $response = $this->sendOrderComplete(
      $this->drOrders[$subscription->getActiveInvoice()->getDrOrderId()]->setState(DrOrder::STATE_COMPLETE),
      $eventId = $this->drHelper->uuid()
    );

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertStatus(400);
    $this->assertTrue($subscription->status == Subscription::STATUS_PROCESSING);
    $this->assertTrue($subscription->getActiveInvoice()->status == Invoice::STATUS_PROCESSING);
    $this->assertDatabaseMissing('dr_events', [
      'event_id' => $eventId
    ]);
    Notification::assertNothingSent();
  }

  public function test_processing_to_failed_error_by_cancel_order()
  {
    $subscription = $this->init_processing();

    $response = $this->cancelOrder($subscription);
    $response->assertStatus(409);
  }

  public function test_processing_to_failed_blocked()
  {
    $subscription = $this->init_processing();

    return $this->onOrderBlocked($subscription);
  }

  public function test_processing_to_failed_cancelled()
  {
    $subscription = $this->init_processing();

    return $this->onOrderCancelled($subscription);
  }

  public function test_processing_to_failed_charge_failed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderChargeFailed($subscription);
  }

  public function test_processing_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_processing();

    Notification::fake();

    Carbon::setTestNow('2023-01-01 00:31:00');
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', Subscription::STATUS_PROCESSING)->count() > 0);

    Notification::assertSentTo(
      new Developer,
      fn (SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_processing_not_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_processing();

    Notification::fake();

    Carbon::setTestNow('2023-01-01 00:29:00');
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', Subscription::STATUS_PROCESSING)->count() > 0);

    Notification::assertNothingSent();
  }
}
