<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveInvoiceCompletedTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active_invoice_completed()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    return $this->onOrderInvoiceCompleted($subscription);
  }

  public function test_active_invoice_completed_to_active_invoice_completing()
  {
    $subscription = $this->init_active_invoice_completed();

    return $this->onSubscriptionExtended($subscription);
  }

  public function test_active_invoice_completed_to_active_invoice_pending()
  {
    $subscription = $this->init_active_invoice_completed();

    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_completed_to_cancelling()
  {
    $subscription = $this->init_active_invoice_completed();

    return $this->cancelSubscription($subscription);
  }

  public function test_active_invoice_completed_to_failed()
  {
    $subscription = $this->init_active_invoice_completed();

    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_invoice_completed_chargeback()
  {
    $subscription = $this->init_active_invoice_completed();

    return $this->onOrderChargeback($subscription);
  }
}
