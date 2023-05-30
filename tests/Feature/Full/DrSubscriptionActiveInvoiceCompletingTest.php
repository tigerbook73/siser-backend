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

  public function test_active_invoice_completing_to_active_invoice_pending_error_same_invoice_id()
  {
    // preparing 1: to completing status
    $subscription = $this->init_active_invoice_completing();

    // preparint 2: fabricate a dr invoice id
    $invoice = $subscription->getActiveInvoice();
    $invoice->setInvoiceId($this->drHelper->uuid())->save();

    // call api
    $response = $this->sendSubscriptionPaymentFailed(
      $this->drHelper->createSubscription($subscription),
      $this->drHelper->createInvoice($subscription, $invoice->getDrInvoiceId())
    );

    // refresh data
    $subscription->refresh();
    $invoice->refresh();

    // assert
    $response->assertSuccessful();
    $this->assertTrue($subscription->status == Subscription::STATUS_ACTIVE);
    $this->assertTrue($subscription->sub_status == Subscription::SUB_STATUS_INVOICE_COMPLETING);
    $this->assertTrue($invoice->status == Invoice::STATUS_COMPLETING);

    return $subscription;
  }
}
