<?php

namespace Tests\Feature;

class AuthAdminLogoutApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthAdminLogoutOk()
  {
    $token = auth('admin')->tokenById($this->user->id);

    $response = $this->postJson("{$this->baseUrl}/logout", [], ['Authorization' => "Bearer $token"]);
    $response->assertStatus(204);
    return $response;
  }
}
