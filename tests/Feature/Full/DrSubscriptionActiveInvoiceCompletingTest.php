<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
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

  public function test_active_invoice_completing_to_cancelling()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->cancelSubscription($subscription);
  }

  public function test_active_invoice_completing_to_cancelling_to_completed()
  {
    $subscription = $this->init_active_invoice_completing();
    $response = $this->cancelSubscription($subscription);

    return $this->onOrderInvoiceCompleted($response->json('id'));
  }

  public function test_active_invoice_completing_to_active_invoice_pending()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_completing_to_active_invoice_completing()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->onSubscriptionExtended($subscription);
  }

  public function test_active_invoice_completing_to_failed()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_invoice_completing_chargeback()
  {
    $subscription = $this->init_active_invoice_completing();

    return $this->onOrderChargeback($subscription);
  }
}
