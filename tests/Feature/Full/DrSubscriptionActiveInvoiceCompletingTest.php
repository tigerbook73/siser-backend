<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveInvoiceCompletingTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active_invoice_completing()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    return $this->onOrderComplete($subscription);
  }

  public function test_active_invoice_completing_to_active_invoice_completed()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->onOrderInvoiceCompleted($subscription);
  }
}
