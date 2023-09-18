<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\Developer;
use App\Notifications\SubscriptionWarning;
use Carbon\Carbon;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Exception;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionProcessingTest extends DrApiTestCase
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
    return $this->onOrderAccept(Subscription::find($response->json('id')));
  }

  public function test_processing_to_completed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderComplete($subscription);
  }

  public function test_processing_to_failed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderChargeCaptureFailed($subscription);
  }

  public function test_processing_to_cancell()
  {
    $subscription = $this->init_processing();

    return $this->cancelSubscription($subscription);
  }

  public function test_processing_to_cancell_refund_to_complete()
  {
    $subscription = $this->init_processing();

    $this->cancelSubscription($subscription, true);

    $subscription->refresh();
    $this->onOrderComplete($subscription);
  }

  public function test_processing_to_cancell_refund_to_failed()
  {
    $subscription = $this->init_processing();

    $this->cancelSubscription($subscription, true);

    $subscription->refresh();
    $this->onOrderChargeCaptureFailed($subscription);
  }

  public function test_processing_order_invoice()
  {
    $subscription = $this->init_processing();
    $invoice = $subscription->getCurrentPeriodInvoice();
    $this->onOrderInvoiceCreated($invoice);
  }

  public function test_processing_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_processing();

    Notification::fake();

    Carbon::setTestNow('2023-01-03 00:31:00');
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PROCESSING)->count() > 0);

    Notification::assertSentTo(
      new Developer,
      fn (SubscriptionWarning $notification) => $notification->type == SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION
    );
  }

  public function test_processing_not_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $response = $this->init_processing();

    Notification::fake();

    Carbon::setTestNow('2023-01-01 23:59:59'); // less than two days
    $this->artisan('subscription:warn-pending')->assertSuccessful();

    $this->assertTrue($this->user->invoices()->where('status', Invoice::STATUS_PROCESSING)->count() > 0);

    Notification::assertNothingSent();
  }
}
