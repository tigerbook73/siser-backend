<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
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
    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_pending_to_active_invoice_completed()
  {
    $subscription = $this->init_active_invoice_pending();

    $this->onSubscriptionExtended($subscription);
    return $this->onOrderComplete($subscription);
  }

  public function test_active_invoice_pending_to_active_invoice_pending()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->onSubscriptionPaymentFailed($subscription);
  }

  public function test_active_invoice_pending_to_cancelling()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->cancelSubscription($subscription);
  }

  public function test_active_invoice_pending_chargeback()
  {
    $subscription = $this->init_active_invoice_pending();

    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->onOrderChargeback($invoice);
    $this->onRefundPending($invoice, true);
    $this->onRefundComplete($invoice);
  }

  public function test_active_invoice_pending_to_failed()
  {
    $subscription = $this->init_active_invoice_pending();

    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_invoice_pending_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_active_invoice_pending();

    Notification::fake();

    Carbon::setTestNow(now()->add(SubscriptionWarning::INVOICE_RENEW_PERIOD)->addSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PENDING)->count() > 0);

    Notification::assertSentTo(
      new Developer,
      fn (SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_active_invoice_pending_no_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_active_invoice_pending();

    Notification::fake();

    Carbon::setTestNow(now()->add(SubscriptionWarning::INVOICE_RENEW_PERIOD)->subSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PENDING)->count() > 0);

    Notification::assertNothingSent();
  }
}
