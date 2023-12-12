<?php

namespace Tests\Feature;

use App\Models\User;

class UserUpdateDetailsApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUpdateTypeOk()
  {
    $user = User::first();

    $data = ['type' => User::TYPE_BLACKLISTED];
    $response = $this->postJson("$this->baseUrl/{$user->id}/details", $data);
    $response->assertSuccessful()->assertJson($data);
    $user->refresh();
    $this->assertEquals($user->type, $data['type']);

    $data = ['type' => User::TYPE_NORMAL];
    $response = $this->postJson("$this->baseUrl/{$user->id}/details", $data);
    $response->assertSuccessful()->assertJson($data);
    $user->refresh();
    $this->assertEquals($user->type, $data['type']);

    $data = ['type' => User::TYPE_STAFF];
    $response = $this->postJson("$this->baseUrl/{$user->id}/details", $data);
    $response->assertSuccessful()->assertJson($data);
    $user->refresh();
    $this->assertEquals($user->type, $data['type']);

    $data = ['type' => User::TYPE_VIP];
    $response = $this->postJson("$this->baseUrl/{$user->id}/details", $data);
    $response->assertSuccessful()->assertJson($data);
    $user->refresh();
    $this->assertEquals($user->type, $data['type']);
  }

  public function testUpdateTypeFailed()
  {
    $user = User::first();

    $data = ['type' => 'abc'];
    $response = $this->postJson("$this->baseUrl/{$user->id}/details", $data);
    $this->assertFailed($response);
  }
}
