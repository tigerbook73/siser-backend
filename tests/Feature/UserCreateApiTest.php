<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Trait\CognitoProviderMockup;

class UserCreateApiTest extends UserTestCase
{
  use CognitoProviderMockup;

  public ?string $role = 'admin';

  public function testUserCreateOk()
  {
    DB::beginTransaction();
    $modelCreateFrom = [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);
    DB::rollBack();

    DB::beginTransaction();
    $modelCreateFrom = [
      "create_from" => "access_token",
      "access_token" => $this->createRandomString(20),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema);
    DB::rollBack();
  }

  public function testUserCreateError()
  {
    $modelCreateFrom = [
      "create_from" => "access_token",
      "username" => $this->getDefaultTestUserName(),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(422);

    $modelCreateFrom = [
      "create_from" => "username",
      "access_token" => $this->createRandomString(20),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(422);

    $modelCreateFrom = [
      "create_from" => "access_token",
      "token" => $this->createRandomString(20),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(422);

    $modelCreateFrom = [
      "create_from" => "user",
      "username" => $this->getDefaultTestUserName(),
    ];
    $response = $this->postJson($this->baseUrl, $modelCreateFrom);
    $response->assertStatus(422);
  }
}
