<?php

namespace Tests\Feature;

class AuthLogoutApiTest extends AuthTestCase
{
  public ?string $role = 'customer';

  public function testAuthLogoutOk()
  {
    $token = auth('api')->tokenById($this->user->id);

    $response = $this->postJson("{$this->baseUrl}/logout", [], ['Authorization' => "Bearer $token"]);
    $response->assertStatus(204);
    return $response;
  }
}
