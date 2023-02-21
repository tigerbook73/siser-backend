<?php

namespace Tests\Feature;

class AccountSubscriptionListApiTest extends AccountSubscriptionTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionListOk()
  {
    $count = $this->user->subscriptions()->count();

    $response = $this->getJson($this->baseUrl);
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => $this->modelSchema
        ]
      ]);

    $this->assertEquals(count($response->json()['data']), $count);

    return $response;
  }
}
