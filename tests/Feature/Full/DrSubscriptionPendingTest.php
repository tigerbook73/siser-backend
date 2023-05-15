<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
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

    $this->assertTrue($this->user->subscriptions()->where('status', 'pending')->count() > 0);

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

    $this->assertTrue($this->user->subscriptions()->where('status', 'pending')->count() > 0);

    Notification::assertNothingSent();
  }
}
