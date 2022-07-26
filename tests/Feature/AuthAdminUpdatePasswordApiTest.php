<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class AuthAdminUpdatePasswordApiTest extends AuthAdminTestCase
{
  public ?string $role = 'admin';

  public function testAuthAdminUpdatePassworkdOk()
  {
    $reqBody = [
      "current_password" => "password",
      "password" => "password1"
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
