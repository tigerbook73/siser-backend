<?php

namespace Tests\Feature;

use Tests\Trait\CognitoProviderMockup;

class UserUpdateApiTest extends UserTestCase
{
  use CognitoProviderMockup;

  public ?string $role = 'admin';

  public function testUserUpdateOk()
  {
    $response = $this->postJson("$this->baseUrl/" . $this->object->id, []);

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testUserUpdateError()
  {
    $response = $this->postJson("$this->baseUrl/0", []);
    $response->assertStatus(404);
  }
}
