<?php

namespace Tests\Feature\Full;

use App\Console\Commands\SubscriptionWarnPending;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveTest_ extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_active()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription(Plan::INTERVAL_MONTH);
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccepted(Subscription::find($response->json('id')));
    $subscription = $this->onOrderComplete($subscription);
    return $subscription;
  }

  public function test_active_to_pdf_created()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getInvoiceByOrderId($subscription->getDrOrderId());

    return $this->onOrderInvoiceCreated($invoice);
  }

  public function test_active_to_cancelling()
  {
    $subscription = $this->init_active();

    return $this->cancelSubscription($subscription, immediate: false);
  }

  public function test_active_to_stopped_refund_success()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->cancelSubscription($subscription, immediate: true);
    $invoice->refresh();
    $this->onRefundPending($invoice);
    $this->onRefundComplete($invoice);

    $invoice->refresh();
    $this->onOrderCreditMemoCreated($invoice);
  }

  public function test_active_to_stopped_refund_failed()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->cancelSubscription($subscription, immediate: true);
    $invoice->refresh();

    $this->onRefundPending($invoice);
    $this->onRefundFailed($invoice);
  }

  public function test_active_do_full_refund_success()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->actingAsAdmin();
    $this->createRefund($invoice, 0, "test reason");
    $invoice->refresh();
    $this->actingAsDefault();

    $this->onRefundPending($invoice);
    $this->onRefundComplete($invoice);
    $invoice->refresh();

    $this->onOrderCreditMemoCreated($invoice);
  }

  public function test_active_do_full_refund_failed()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->actingAsAdmin();
    $this->createRefund($invoice, 0, "test reason");
    $invoice->refresh();
    $this->actingAsDefault();

    $this->onRefundPending($invoice);
    $this->onRefundFailed($invoice);
  }

  public function test_active_do_partly_refund()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    // step1: refund 1/2
    $this->actingAsAdmin();
    $this->createRefund($invoice, round($invoice->total_amount / 2, 2), "1/2 refund");
    $invoice->refresh();
    $refund = $invoice->getActiveRefund();
    $this->actingAsDefault();


    // step1: refund complete & order refunded
    $this->onRefundPending($invoice);
    $this->onRefundComplete($invoice);
    $invoice->refresh();
    $refund->refresh();

    // step1: assert
    $this->assertEquals($invoice->status, Invoice::STATUS_PARTLY_REFUNDED);
    $this->assertEquals($refund->status, Refund::STATUS_COMPLETED);

    // step2: refund another 1/2
    $this->actingAsAdmin();
    $this->createRefund($invoice, 0, "another 1/2 refund");
    $invoice->refresh();
    $refund = $invoice->getActiveRefund();
    $this->actingAsDefault();


    // step2: refund complete & order refunded
    $this->onRefundPending($invoice);
    $this->onRefundComplete($invoice);
    $invoice->refresh();
    $refund->refresh();

    // step2: assert
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDED);
    $this->assertEquals($refund->status, Refund::STATUS_COMPLETED);
  }

  public function test_active_reminder()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
    $this->onInvoiceOpen($subscription);
  }

  public function test_active_to_failed()
  {
    $subscription = $this->init_active();

    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_to_lapsed_failed()
  {
    $subscription = $this->init_active();

    return $this->onSubscriptionLapsed($subscription);
  }

  public function test_active_source_invalid()
  {
    $subscription = $this->init_active();

    return $this->onSubscriptionSourceInvalid($subscription);
  }

  public function test_active_to_reminder_failed()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
    $this->onInvoiceOpen($subscription);
    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_to_reminder_extended()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
    $this->onInvoiceOpen($subscription);
    $this->onSubscriptionExtended($subscription);
    return $this->onOrderComplete($subscription);
  }

  public function test_active_chargeback()
  {
    $subscription = $this->init_active();

    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->onOrderChargeback($invoice);
    $this->onRefundPending($invoice, true);
    $this->onRefundComplete($invoice);
  }

  public function test_active_admin_cancel_and_stop()
  {
    $subscription = $this->init_active();

    $this->actingAsAdmin();
    $this->adminCancelSubscription($subscription);
    $this->actingAsDefault();

    $this->actingAsAdmin();
    $this->adminStopSubscription($subscription);
    $this->actingAsDefault();
  }

  public function test_active_admin_cancel_duplicated()
  {
    $subscription = $this->init_active();

    $this->actingAsAdmin();
    $this->adminCancelSubscription($subscription);
    $this->actingAsDefault();

    $this->actingAsAdmin();
    $this->adminCancelSubscription($subscription);
    $this->actingAsDefault();
  }

  public function test_active_admin_stop_error()
  {
    $subscription = $this->init_active();

    $this->actingAsAdmin();
    $this->adminStopSubscription($subscription);
    $this->actingAsDefault();
  }

  public function test_active_dispute_cancel()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);

    // try to cancel subscription
    $this->cancelSubscription($subscription, immediate: false);
  }

  public function test_active_dispute_cancel_immediate()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);

    // try to cancel subscription
    $this->cancelSubscription($subscription, immediate: true);
  }

  public function test_active_dispute_resolved_cancel()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);
    $invoice = $this->onOrderDisputeResolved($invoice);
    $subscription->refresh();

    $this->cancelSubscription($subscription, immediate: true);
  }

  public function test_active_dispute_refund()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);

    // try to cancel subscription
    $this->actingAsAdmin();
    $response = $this->postJson("/api/v1/refunds", [
      'invoice_id' => $invoice->id,
      'amount' => $invoice->total_amount,
      'reason' => 'test',
    ]);

    $response->assertStatus(400);
  }

  public function test_active_dispute_resolved_refund()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);
    $invoice = $this->onOrderDisputeResolved($invoice);

    // try to cancel subscription
    $this->actingAsAdmin();
    $this->createRefund($invoice, 0, "test reason");
  }

  public function test_active_chargeback_refund()
  {
    $subscription = $this->init_active();

    // dispute order
    $invoice = $subscription->getCurrentPeriodInvoice();
    $invoice = $this->onOrderDispute($invoice);
    $invoice = $this->onOrderChargeback($invoice);

    // try to cancel subscription
    $this->actingAsAdmin();
    $response = $this->postJson("/api/v1/refunds", [
      'invoice_id' => $invoice->id,
      'amount' => $invoice->total_amount,
      'reason' => 'test',
    ]);

    $response->assertStatus(400);
  }

  public function test_active_subscription_hanging_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_active();

    Notification::fake();

    Carbon::setTestNow($subscription->next_invoice_date->add(SubscriptionWarnPending::SUBSCRIPTION_HANGING_PERIOD)->addSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    Notification::assertSentTo(
      new Developer,
      fn(SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_active_subscription_hanging_no_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_active();

    Notification::fake();

    Carbon::setTestNow($subscription->next_invoice_date->add(SubscriptionWarnPending::SUBSCRIPTION_HANGING_PERIOD)->subSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    Notification::assertNothingSent();
  }
}
