<?php

namespace Tests\Feature;

class AuthAdminUpdatePasswordApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthAdminUpdatePassworkdOk()
  {
    $reqBody = [
      "current_password" => "password",
      "password" => "~Password1"
    ];

    $response = $this->postJson("{$this->baseUrl}/update-password", $reqBody);
    $response->assertStatus(204);

    $credentials = [
      'email' => $this->object->email,
      'password' => $reqBody['password'],
    ];
    $this->assertTrue(!!auth('admin')->attempt($credentials));
  }
}
