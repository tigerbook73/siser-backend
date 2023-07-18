<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\User;

class AccountSubscriptionCreateApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionCreateOk()
  {
    $this->createOrUpdateBillingInfo();

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
    $this->createOrUpdateBillingInfo();

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

  public function testAccountSubscriptionCreateWithoutMachine()
  {
    $this->createOrUpdateBillingInfo();

    $this->user->machines()->delete();

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

  public function testAccountSubscriptionCreateBlocked()
  {
    $this->createOrUpdateBillingInfo();

    $plan = Plan::public()->first();
    $coupon = Coupon::public()->first();

    // mock up
    $this->user->type = User::TYPE_BLACKLISTED;
    $this->user->save();

    $response = $this->postJson('/api/v1/account/subscriptions', [
      "plan_id"     => $plan->id,
      "coupon_id"   => $coupon->id
    ]);

    $response->assertStatus(400);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
