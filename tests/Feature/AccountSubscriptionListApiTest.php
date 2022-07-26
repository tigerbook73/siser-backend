<?php

namespace Tests\Feature;

use App\Models\User;

class AccountSubscriptionListApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testAccountSubscriptionListOk()
  {
    $count = $this->object->machines()->count();

    $response = $this->getJson("{$this->baseUrl}/machines");
    $response->assertStatus(200)
      ->assertJsonStructure([
        'data' => [
          '*' => $this->machineSchema
        ]
      ]);

    $this->assertEquals(count($response->json()['data']), $count);

    return $response;
  }

  // TODO: more tests to come
}
