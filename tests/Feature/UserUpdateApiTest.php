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
    $modelUpdate = [
      "name" => "user1.test",
      "given_name" => "User1",
      "family_name" => "Test",
      "full_name" => "User1 Test",
      "email" => "user1.test@iifuture.com",
      "country_code" => 'AU',
      "language_code" => 'en',
      "subscription_level" => 1,
      "license_count" => 2,
    ];

    $response = $this->postJson("$this->baseUrl/" . $this->object->id, []);

    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson(array_diff_key($modelUpdate, array_flip($this->hiden)));

    return $response;
  }

  // TODO: more tests to come
}
