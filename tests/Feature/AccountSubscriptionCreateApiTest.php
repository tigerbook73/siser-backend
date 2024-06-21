<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;

class AccountSubscriptionCreateApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionCreateOk()
  {
    $this->createOrUpdateBillingInfo();

    $response = $this->createSubscription(Plan::INTERVAL_MONTH);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testAccountSubscriptionCreateOk2()
  {
    $this->createOrUpdateBillingInfo();

    $response = $this->createSubscription();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testAccountSubscriptionCreateWithoutMachine()
  {
    $this->createOrUpdateBillingInfo();

    $this->user->machines()->delete();

    $response = $this->createSubscription(Plan::INTERVAL_MONTH);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testAccountSubscriptionCreateBlocked()
  {
    $this->createOrUpdateBillingInfo();
    // mock up
    $this->user->type = User::TYPE_BLACKLISTED;
    $this->user->save();

    $plan = Plan::public()->where('interval', Plan::INTERVAL_MONTH)->first();
    $response = $this->postJson('/api/v1/account/subscriptions', [
      "plan_id"     => $plan->id,
    ]);

    $response->assertStatus(400);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
