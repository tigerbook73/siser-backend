<?php

namespace Tests\Feature;

use App\Models\User;

class UserSubscriptionListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserSubscriptionListOk()
  {
    $count = $this->object->subscriptions()->count();

    $response = $this->getJson("{$this->baseUrl}/{$this->object->id}/subscriptions");
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => $this->subscriptionSchema
        ]
      ]);

    $this->assertEquals(count($response->json()['data']), $count);

    return $response;
  }

  // TODO: more tests to come
}
