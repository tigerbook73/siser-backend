<?php

namespace Tests\Feature;

use App\Models\User;

class UserUpdateApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserUpdateOk()
  {
    $modelUpdate = [
      "name" => "john.smith",
      "given_name" => "John",
      "family_name" => "Smith",
      "full_name" => "John Smith",
      "email" => "john.smith@gmail.com",
      "country_code" => 'US',
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
