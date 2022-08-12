<?php

namespace Tests\Feature;

class AuthAdminLoginApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthLoginOk()
  {
    $response = $this->postJson("{$this->baseUrl}/login", [
      'email' => $this->object->email,
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

  public function testAuthLoginNoEmailError()
  {
    $response = $this->postJson("{$this->baseUrl}/login", [
      'email' => '',
      'password' => 'password',
    ]);
    $response->assertStatus(422);
    $response->assertJsonPath('errors.email', ['The email field is required.']);

    return $response;
  }

  public function testAuthLoginInvalidEmailError()
  {
    $response = $this->postJson("{$this->baseUrl}/login", [
      'email' => 'admin',
      'password' => 'password',
    ]);
    $response->assertStatus(422);
    $response->assertJsonPath('errors.email', ['The email must be a valid email address.']);

    return $response;
  }

  public function testAuthLoginNoPasswordError()
  {
    $response = $this->postJson("{$this->baseUrl}/login", [
      'email' => $this->object->email,
      'password' => '',
    ]);
    $response->assertStatus(422);
    $response->assertJsonPath('errors.password', ['The password field is required.']);

    return $response;
  }
}
