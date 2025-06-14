<?php

namespace Tests\Feature;

class AuthRefreshApiTest extends AuthTestCase
{
  public ?string $role = 'customer';

  public function testAuthRefreshOk()
  {
    $modelSchema = [
      "access_token",
      "token_type",
      "expires_in",
    ];

    $token = auth('api')->tokenById($this->user->id);

    $response = $this->postJson("{$this->baseUrl}/refresh", [], ['Authorization' => "Bearer $token"]);
    $response->assertStatus(200)
      ->assertJsonStructure($modelSchema);

    return $response;
  }
}
