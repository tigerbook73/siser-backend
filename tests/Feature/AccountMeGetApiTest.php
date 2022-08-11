<?php

namespace Tests\Feature;

class AccountMeGetApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testAccountGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/me");

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson([(new $this->model)->getKeyName() => $this->object->id]);

    return $response;
  }
}
