<?php

namespace Tests\Feature;

use App\Models\User;

class AuthAdminLoginApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthLoginOk()
  {
    $response = $this->postJson("{$this->baseUrl}/login", [
      'email' => 'admin@iifuture.com',
      'password' => 'password',
    ]);
    $response->assertStatus(200)
      ->assertJsonStructure([
        "access_token",
        "token_type",
        "expires_in",
      ]);
    return $response;
  }
}
