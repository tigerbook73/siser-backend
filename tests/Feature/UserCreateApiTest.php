<?php

namespace Tests\Feature;

use App\Models\User;

class UserCreateApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserCreateOk()
  {
    $modelCreateFrom = [
      "create_from" => "username",
      "username" => "user2.test",
    ];

    $modelCreate = [
      "name" => "user2.test",
      "given_name" => "User2",
      "family_name" => "Test",
      "full_name" => "User2 Test",
      "email" => "user2.test@iifuture.com",
      // "phone_number" => null,
      "country_code" => 'AU',
      "language_code" => 'en',
      "subscription_level" => 0,
      "license_count" => 0,
    ];

    $response = $this->postJson($this->baseUrl, $modelCreateFrom);

    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson(array_diff_key($modelCreate, array_flip($this->hiden)));

    return $response;
  }

  // TODO: more tests to come
}
