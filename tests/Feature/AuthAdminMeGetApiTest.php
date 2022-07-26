<?php

namespace Tests\Feature;

use App\Models\User;

class AuthAdminMeGetApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthAdminMeGetOk()
  {
    $response = $this->postJson("{$this->baseUrl}/me");

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson([(new $this->model)->getKeyName() => $this->object->id]);

    return $response;
  }
}
