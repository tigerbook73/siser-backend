<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveCancellingTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active_cancelling()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    $this->cancelSubscription($subscription->id);
    return $subscription->refresh();
  }

  public function test_active_cancelling_to_stopped()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_active_cancelling();

    Carbon::setTestNow('2023-02-01 00:31:00');
    $this->artisan('subscription:stop-cancelled')->assertSuccessful();

    $subscription->refresh();
    $this->assertTrue($subscription->status == Subscription::STATUS_STOPPED);
  }

  public function test_active_cancelling_chargeback()
  {
    $subscription = $this->init_active_cancelling();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->onOrderChargeback($invoice);
    $this->onRefundPending($invoice, true);
    $this->onRefundComplete($invoice);
  }

  public function test_actived_cancelling_to_pdf_created()
  {
    $subscription = $this->init_active_cancelling();
    $invoice = $subscription->getInvoiceByOrderId($subscription->getDrOrderId());
    $this->onOrderInvoiceCreated($invoice);
  }
}
