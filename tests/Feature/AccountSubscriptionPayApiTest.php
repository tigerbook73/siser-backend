<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;

class AccountSubscriptionPayApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionPayOk()
  {
    $this->createBillingInfo();
    $this->createPaymentMethod();

    $plan = Plan::public()->first();
    $createResponse = $this->createSubscription([
      "plan_id"   => $plan->id
    ]);

    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json()['id'] . '/pay');
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
