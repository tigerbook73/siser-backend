<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrSubscriptionActiveTest extends DrApiTestCase
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
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
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

    return $this->cancelSubscription($subscription);
  }

  public function test_active_to_stopped_refund_success()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->cancelSubscription($subscription, true);
    $invoice->refresh();
    $this->onRefundPending($invoice);
    $this->onRefundComplete($invoice);

    $invoice->refresh();
    $this->onOrderRefunded($invoice);

    $invoice->refresh();
    $this->onOrderCreditMemoCreated($invoice);
  }

  public function test_active_to_stopped_refund_failed()
  {
    $subscription = $this->init_active();
    $invoice = $subscription->getCurrentPeriodInvoice();

    $this->cancelSubscription($subscription, true);
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

    $this->onOrderRefunded($invoice);
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

    $this->onOrderRefunded($invoice, round($invoice->total_amount / 2, 2));
    $invoice->refresh();

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

    $this->onOrderRefunded($invoice);
    $invoice->refresh();

    // step2: assert
    $this->assertEquals($invoice->status, Invoice::STATUS_REFUNDED);
    $this->assertEquals($refund->status, Refund::STATUS_COMPLETED);
  }

  public function test_active_reminder()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
  }

  public function test_active_to_failed()
  {
    $subscription = $this->init_active();

    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_to_reminder_failed()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
    return $this->onSubscriptionFailed($subscription);
  }

  public function test_active_to_reminder_extended()
  {
    $subscription = $this->init_active();

    $this->onSubscriptionReminder($subscription);
    return $this->onSubscriptionExtended($subscription);
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
}
