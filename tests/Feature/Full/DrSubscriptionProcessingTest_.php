<?php

namespace Tests\Feature\Full;

use App\Console\Commands\SubscriptionWarnPending;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Exception;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionProcessingTest_ extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_processing()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription();
    $response = $this->paySubscription($response->json('id'));
    return $this->onOrderAccepted(Subscription::find($response->json('id')));
  }

  public function test_processing_to_completed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderComplete($subscription);
  }

  public function test_processing_to_charge_capture_failed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderChargeCaptureFailed($subscription);
  }

  public function test_processing_to_charge_capture_completed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderChargeCaptureCompleted($subscription);
  }

  public function test_processing_to_cancell()
  {
    $subscription = $this->init_processing();

    return $this->cancelSubscription($subscription, immediate: false);
  }

  public function test_processing_to_cancell_refund_to_complete()
  {
    $subscription = $this->init_processing();

    $this->cancelSubscription($subscription, immediate: true);

    $subscription->refresh();
    $this->onOrderComplete($subscription);
  }

  public function test_processing_order_invoice()
  {
    $subscription = $this->init_processing();
    $invoice = $subscription->getCurrentPeriodInvoice();
    $this->onOrderInvoiceCreated($invoice);
  }

  public function test_processing_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->init_processing();

    Notification::fake();
    $this->mockGetOrder(); // processing status

    Carbon::setTestNow(now()->add(SubscriptionWarnPending::INVOICE_PROCESSING_PERIOD)->addSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PROCESSING)->count() > 0);

    Notification::assertSentTo(
      new Developer,
      fn(SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_processing_expired_try_complete()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $subscription = $this->init_processing();

    $this->drHelper->getOrder($subscription->getDrOrderId())->setState(DrOrder::STATE_COMPLETE);

    Notification::fake();
    $this->mockGetOrder(); // processing status

    Carbon::setTestNow(now()->add(SubscriptionWarnPending::INVOICE_PROCESSING_PERIOD)->addSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PROCESSING)->count() == 0);

    Notification::assertNothingSent();
  }

  public function test_processing_no_notification()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->init_processing();

    Notification::fake();

    Carbon::setTestNow(now()->add(SubscriptionWarnPending::INVOICE_PROCESSING_PERIOD)->subSecond());
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PROCESSING)->count() > 0);

    Notification::assertNothingSent();
  }
}
