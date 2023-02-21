<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;

class AccountSubscriptionCreateApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionCreateOk()
  {
    $this->createBillingInfo();

    $plan = Plan::public()->first();
    $response = $this->createSubscription([
      "plan_id"   => $plan->id
    ]);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testAccountSubscriptionCreateOk2()
  {
    $this->createBillingInfo();

    $plan = Plan::public()->first();
    $coupon = Coupon::public()->first();

    $response = $this->createSubscription([
      "plan_id"     => $plan->id,
      "coupon_id"   => $coupon->id
    ]);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
