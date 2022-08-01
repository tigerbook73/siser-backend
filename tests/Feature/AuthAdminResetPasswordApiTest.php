<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class AuthAdminResetPasswordApiTest extends AuthAdminTestCase
{
  public ?string $role = null;


  /**
   * helper function to generate reset token
   */
  public function getResetToken()
  {
    $fake = Notification::fake();
    $response = $this->postJson("{$this->baseUrl}/forgot-password", [
      'email' => $this->object->email,
    ]);
    $notification = $fake->sent($this->object, ResetPassword::class)[0];

    return [
      'email' => $this->object->email,
      'token' => $notification->token,
    ];
  }

  public function testAuthAdminResetPassworkdOk()
  {
    $token = $this->getResetToken();
    $token['password'] = "~Password1";

    $response = $this->postJson("{$this->baseUrl}/reset-password", $token);
    $response->assertStatus(204);

    unset($token['token']);
    $this->assertTrue(!!auth('admin')->attempt($token));
  }
}
