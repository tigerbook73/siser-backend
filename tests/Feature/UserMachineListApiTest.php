<?php

namespace Tests\Feature;

class UserMachineListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserMachineListSuccess()
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

  public function testUserMachineListError()
  {
    $response = $this->getJson("{$this->baseUrl}/x/machines");
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['user_id' => 'The user id must be an integer.']);

    $response = $this->getJson("{$this->baseUrl}//machines");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}/{$this->object->id}/apparatus");
    $response->assertStatus(404);
  }
}
