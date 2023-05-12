<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveInvoiceOverdueTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active_invoice_overdue()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    $subscription = $this->onOrderInvoiceCompleted($subscription);
    $subscription = $this->onInvoiceOpen($subscription);
    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_overdue_to_active_invoice_completing()
  {
    $subscription = $this->init_active_invoice_overdue();

    return $this->onSubscriptionExtended($subscription);
  }

  public function test_active_invoice_overdue_to_failed()
  {
    $subscription = $this->init_active_invoice_overdue();

    return $this->onSubscriptionFailed($subscription);
  }
}
