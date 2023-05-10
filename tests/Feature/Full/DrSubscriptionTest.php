<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;
use Tests\DR\DrTestTrait;

class DrSubscriptionTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  use DrTestTrait;

  /**
   * the following is subscription path
   */
  public function test_init_to_billing_info()
  {
    return $this->createOrUpdateBillingInfo();
  }

  public function test_billing_info_to_payment_method()
  {
    $this->createOrUpdateBillingInfo();
    return $this->createOrUpdatePaymentMethod();
  }

  public function test_payment_method_to_draft()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    return $this->createSubscription();
  }

  public function test_draft_timeout()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->test_payment_method_to_draft();

    Carbon::setTestNow('2023-01-01 00:31:00');
    $this->artisan('subscription:clean-draft')->assertSuccessful();
    $this->assertTrue($this->user->subscriptions()->where('status', 'draft')->count() <= 0);
  }

  public function test_draft_delete()
  {
    $response = $this->test_payment_method_to_draft();
    $this->deleteSubscription($response->json('id'));
  }

  public function test_draft_to_pending()
  {
    $response = $this->test_payment_method_to_draft();

    return $this->paySubscription($response->json('id'));
  }

  public function test_pending_to_processing()
  {
    $response = $this->test_draft_to_pending();

    return $this->onOrderAccept(Subscription::find($response->json('id')));
  }

  public function test_processing_to_active_invoice_completing()
  {
    $subscription = $this->test_pending_to_processing();

    return $this->onOrderComplete($subscription);
  }

  public function test_active_invoice_completing_to_active_invoice_completed()
  {
    $subscription = $this->test_processing_to_active_invoice_completing();

    return $this->onOrderInvoiceCompleted($subscription);
  }

  public function test_active_invoice_completed_to_active_invoice_open()
  {
    $subscription = $this->test_active_invoice_completing_to_active_invoice_completed();

    return $this->onInvoiceOpen($subscription);
  }

  public function test_active_invoice_open_to_active_invoice_overdue()
  {
    $subscription = $this->test_active_invoice_completed_to_active_invoice_open();

    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_open_to_active_invoice_completing()
  {
    $subscription = $this->test_active_invoice_completed_to_active_invoice_open();

    return $this->onSubscriptionExtended($subscription);
  }
}
