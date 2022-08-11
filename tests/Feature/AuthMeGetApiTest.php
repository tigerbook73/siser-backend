<?php

namespace Tests\Feature;

class AuthMeGetApiTest extends AuthTestCase
{
  public ?string $role = 'customer';

  public function testAuthMeGetOk()
  {
    $response = $this->postJson("{$this->baseUrl}/me");

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson([(new $this->model)->getKeyName() => $this->object->id]);

    return $response;
  }
}
