<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class AccountSubscriptionPayApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionPayOk()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();

    $createResponse = $this->createSubscription(Plan::INTERVAL_MONTH);

    /**
     * mock up functions
     */
    $subscription = Subscription::find($createResponse->json()['id']);
    $this->mockAttachCheckoutSource();
    $this->mockUpdateCheckoutTerms();
    $this->mockConvertCheckoutToOrder();

    $response = $this->postJson(
      $this->baseUrl . '/' . $createResponse->json()['id'] . '/pay',
      ['terms' => 'This is test terms ...']
    );
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testAccountSubscriptionPayBlocked()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();

    $createResponse = $this->createSubscription(Plan::INTERVAL_MONTH);

    // mock up
    $this->user->type = User::TYPE_BLACKLISTED;
    $this->user->save();

    $subscription = Subscription::find($createResponse->json()['id']);
    $response = $this->postJson(
      $this->baseUrl . '/' . $subscription->id . '/pay',
      ['terms' => 'This is test terms ...']
    );
    $response->assertStatus(400);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
