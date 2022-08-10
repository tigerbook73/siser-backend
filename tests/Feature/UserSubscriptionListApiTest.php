<?php

namespace Tests\Feature;

class UserSubscriptionListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserSubscriptionListSuccess()
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

  public function testUserSubscriptionListError()
  {
    $response = $this->getJson("{$this->baseUrl}/x/subscriptions");
    $response->assertStatus(422);

    $response = $this->getJson("{$this->baseUrl}//moneypledged");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}/{$this->object->id}/moneypledged");
    $response->assertStatus(404);
  }
}
