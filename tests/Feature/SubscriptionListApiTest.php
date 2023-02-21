<?php

namespace Tests\Feature;

use App\Models\User;

class SubscriptionListApiTest extends SubscriptionTestCase
{
  public ?string $role = 'admin';

  public function testSubscriptionListSuccess()
  {
    $user = User::first();
    $count = $user->subscriptions()->count();

    $response = $this->getJson("{$this->baseUrl}?user_id={$user->id}");
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => $this->modelSchema
        ]
      ]);

    $this->assertEquals(count($response->json()['data']), $count);

    return $response;
  }

  public function testUserSubscriptionListError()
  {
    $user = User::first();

    // TODO: mockup code issues
    $this->markTestIncomplete('mockup code issues');

    $response = $this->getJson("{$this->baseUrl}/x/subscriptions");
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['user_id' => 'The user id must be an integer.']);

    $response = $this->getJson("{$this->baseUrl}//moneypledged");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}/{$user->id}/moneypledged");
    $response->assertStatus(404);
  }
}
