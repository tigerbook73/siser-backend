<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveInvoicePendingTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active_invoice_pending()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    $subscription = $this->onOrderInvoiceCompleted($subscription);
    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_pending_to_active_invoice_completing()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->onSubscriptionExtended($subscription);
  }

  public function test_active_invoice_pending_to_active_invoice_pending()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_pending_to_failed()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->onSubscriptionFailed($subscription);
  }
}
