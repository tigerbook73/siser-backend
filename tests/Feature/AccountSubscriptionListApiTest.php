<?php

namespace Tests\Feature;

class AccountSubscriptionListApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionListOk()
  {
    $count = $this->object->subscriptions()->count();

    $response = $this->getJson("{$this->baseUrl}/subscriptions");
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => $this->subscriptionSchema
        ]
      ]);

    $this->assertEquals(count($response->json()['data']), $count);

    return $response;
  }
}
