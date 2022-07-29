<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Trait\CognitoProviderMockup;

class UserCreateApiTest extends UserTestCase
{
  use CognitoProviderMockup;

  public ?string $role = 'admin';

  public function testUserCreateOk()
  {
    $modelCreateFrom = [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ];

    $response = $this->postJson($this->baseUrl, $modelCreateFrom);

    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  // TODO: more tests to come
}
