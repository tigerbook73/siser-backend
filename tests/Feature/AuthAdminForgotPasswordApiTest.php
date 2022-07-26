<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class AuthAdminForgotPasswordApiTest extends AuthAdminTestCase
{
  public ?string $role = null;

  public function testAuthAdminForgotPasswordOk()
  {
    $fake = Notification::fake();

    $response = $this->postJson("{$this->baseUrl}/forgot-password", [
      'email' => $this->object->email,
    ]);
    $response->assertStatus(204);

    $fake->assertCount(1);
    $fake->assertSentTo($this->object, ResetPassword::class);

    return $response;
  }
}
