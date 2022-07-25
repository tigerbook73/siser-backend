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
      "username" => "user.test",
    ];

    $modelCreate = [
      "name" => "user.test",
      "cognito_id" => "d24c89f1-75ac-4a26-8cde-1979c37c11d6",
      "given_name" => "user",
      "family_name" => "test",
      "full_name" => "user test",
      "email" => "user.test@iifuture.com",
      // "phone_number" => null,
      "country_code" => 'US',
      "language_code" => 'en',
      "subscription_level" => 0
    ];

    $response = $this->postJson($this->baseUrl, $modelCreateFrom);

    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson(array_diff_key($modelCreate, array_flip($this->hiden)));

    return $response;
  }

  // TODO: more tests to come
}
