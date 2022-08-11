<?php

namespace Tests\Feature;

class AccountMachineListApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testAccountMachineListOk()
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
}
