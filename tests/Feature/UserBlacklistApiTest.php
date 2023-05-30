<?php

namespace Tests\Feature;

use App\Models\User;

class UserBlacklistApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testBlacklistTrue()
  {
    $user = User::first();
    $data = ['blacklisted' => true];

    $response = $this->postJson("$this->baseUrl/{$user->id}/blacklist", $data);

    $response->assertSuccessful()->assertJson($data);

    $user->refresh();
    $this->assertTrue($user->blacklisted);
  }

  public function testBlacklistFalse()
  {
    $user = User::first();
    $data = ['blacklisted' => false];

    $response = $this->postJson("$this->baseUrl/{$user->id}/blacklist", $data);

    $response->assertSuccessful()->assertJson($data);

    $user->refresh();
    $this->assertFalse($user->blacklisted);
  }
}
