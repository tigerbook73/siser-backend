<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
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
}
