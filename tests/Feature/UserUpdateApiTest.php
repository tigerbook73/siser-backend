<?php

namespace Tests\Feature;

use App\Models\User;
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

  // TODO: more tests to come
}
