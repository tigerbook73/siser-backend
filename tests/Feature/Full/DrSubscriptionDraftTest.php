<?php

namespace Tests\Feature\Full;

use Carbon\Carbon;
use Exception;
use Tests\DR\DrApiTestCase;

class DrSubscriptionDraftTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_draft()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    return $this->createSubscription();
  }

  public function test_draft_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->init_draft();

    Carbon::setTestNow('2023-01-01 00:31:00');
    $this->artisan('subscription:clean-draft')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', 'draft')->count() <= 0);
  }

  public function test_draft_timeout_not_expired()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->init_draft();

    Carbon::setTestNow('2023-01-01 00:29:00');
    $this->artisan('subscription:clean-draft')->assertSuccessful();

    $this->assertTrue($this->user->subscriptions()->where('status', 'draft')->count() > 0);
  }

  public function test_draft_delete()
  {
    $response = $this->init_draft();

    $this->deleteSubscription($response->json('id'));
  }

  public function test_draft_to_pending()
  {
    $response = $this->init_draft();

    $this->paySubscription($response->json('id'));
  }

  public function test_draft_pay_error_exception_open()
  {
    // prepare
    $response = $this->init_draft();
    $subscription = $this->user->getDraftSubscriptionById($response->json('id'));

    // mockup 
    $this->drMock
      ->shouldReceive('attachCheckoutSource')
      ->once()
      ->andThrow(new Exception('Test', 400));

    // call api
    $response = $this->postJson(
      "/api/v1/account/subscriptions/$subscription->id/pay",
      ['terms' => 'This is test terms ...']
    );

    // refresh data
    $subscription->refresh();

    // assert
    $response->assertStatus(400);
    $this->assertTrue($subscription->status == 'draft');
    $this->assertDatabaseHas('critical_sections', [
      'type' => 'subscription',
      'status' => 'open',
      'object_id' => $subscription->id
    ]);
  }
}
