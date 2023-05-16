<?php

namespace Tests\Feature\Full;

use App\Models\Subscription;
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

  public function test_processing_to_active_invoice_completing()
  {
    $subscription = $this->init_processing();

    return $this->onOrderComplete($subscription);
  }

  public function test_processing_to_active_invoice_completing_error()
  {
    $subscription = $this->init_processing();

    // mock up
    $this->drMock
      ->shouldReceive('activateSubscription')
      ->once()
      ->andThrow(new Exception('test', 444));
    Notification::fake();

    // call api
    $response = $this->sendOrderComplete($this->drHelper->createOrder(
      $subscription,
      null,
      DrOrder::STATE_COMPLETE
    ), $eventId = $this->drHelper->uuid());

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertStatus(400);
    $this->assertTrue($subscription->status == 'processing');
    $this->assertTrue($subscription->getActiveInvoice() == null);
    $this->assertDatabaseHas('critical_sections', [
      'type' => 'subscription',
      'status' => 'open',
      'object_id' => $subscription->id
    ]);
    $this->assertDatabaseMissing('dr_events', [
      'event_id' => $eventId
    ]);
    Notification::assertNothingSent();
  }

  public function test_processing_to_failed_blocked()
  {
    $subscription = $this->init_processing();

    return $this->onOrderBlocked($subscription);
  }

  public function test_processing_to_failed_cancelled()
  {
    $subscription = $this->init_processing();

    return $this->onOrderCancelled($subscription);
  }

  public function test_processing_to_failed_charge_failed()
  {
    $subscription = $this->init_processing();

    return $this->onOrderChargeFailed($subscription);
  }
}
