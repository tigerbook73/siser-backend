<?php

namespace Tests\Feature;

use App\Models\User;

class UserMachineListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserMachineListOk()
  {
    $count = $this->object->machines()->count();

    $response = $this->getJson("{$this->baseUrl}/{$this->object->id}/machines");
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
