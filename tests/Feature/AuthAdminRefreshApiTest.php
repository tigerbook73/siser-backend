<?php

namespace Tests\Feature;

use App\Models\User;

class AuthAdminRefreshApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAdminAuthRefreshOk()
  {
    $modelSchema = [
      "access_token",
      "token_type",
      "expires_in",
    ];

    $token = auth('admin')->tokenById($this->user->id);

    $response = $this->postJson("{$this->baseUrl}/refresh", [], ['Authorization' => "Bearer $token"]);
    $response->assertStatus(200)
      ->assertJsonStructure($modelSchema);

    return $response;
  }
}
