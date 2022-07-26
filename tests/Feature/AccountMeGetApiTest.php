<?php

namespace Tests\Feature;

use App\Models\User;

class AccountMeGetApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testUserGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/me");

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson([(new $this->model)->getKeyName() => $this->object->id]);

    return $response;
  }
}
