<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
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

  public function test_processing_to_active_invoice_completing()
  {
    $subscription = $this->init_processing();

    return $this->onOrderComplete($subscription);
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
}
