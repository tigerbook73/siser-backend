<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;

class AccountSubscriptionPayApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionPayOk()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();

    $plan = Plan::public()->first();
    $createResponse = $this->createSubscription([
      "plan_id"   => $plan->id
    ]);

    /**
     * mock up functions
     */
    $subscription = Subscription::find($createResponse->json()['id']);
    $this->mockAttachCheckoutSource();
    $this->mockUpdateCheckoutTerms($subscription);
    $this->mockConvertCheckoutToOrder($subscription);

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

    $plan = Plan::public()->first();
    $createResponse = $this->createSubscription([
      "plan_id"   => $plan->id
    ]);

    // mock up
    $this->user->blacklisted = true;
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
