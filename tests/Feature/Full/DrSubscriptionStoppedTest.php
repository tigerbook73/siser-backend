<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionStoppedTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_stopped()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');

    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    $this->cancelSubscription($subscription->id);

    Carbon::setTestNow('2023-02-01 00:31:00');
    $this->artisan('subscription:stop-cancelled')->assertSuccessful();

    $subscription->refresh();
    $this->assertTrue($subscription->status == Subscription::STATUS_STOPPED);
    return $subscription;
  }

  public function test_stopped_chargeback()
  {
    $subscription = $this->init_stopped();

    $this->onOrderChargeback($subscription);
  }
}
