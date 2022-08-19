<?php

namespace Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class AuthAdminForgotPasswordApiTest extends AuthAdminTestCase
{
  public ?string $role = null;

  /**
   * success test case
   */
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

  /**
   * error test cases
   */
  public function testAuthAdminForgotPasswordNoEmailError()
  {
    $response = $this->postJson("{$this->baseUrl}/forgot-password", [
      'email' => '',
    ]);
    $response->assertStatus(422);
    $response->assertJsonPath('errors.email', ['The email field is required.']);

    return $response;
  }

  public function testAuthAdminForgotPasswordInvalidEmailError()
  {
    $response = $this->postJson("{$this->baseUrl}/forgot-password", [
      'email' => 'test',
    ]);
    $response->assertStatus(422);
    $response->assertJsonPath('errors.email', ['The email must be a valid email address.']);

    return $response;
  }
}
