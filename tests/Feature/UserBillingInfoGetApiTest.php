<?php

namespace Tests\Feature;

use App\Models\User;

class UserBillingInfoGetApiTest extends UserBillingInfoTestCase
{
  public ?string $role = 'admin';

  public function testUserBillingInfoGetOk()
  {
    $user = User::first();

    $response = $this->getJson("{$this->baseUrl}/{$user->id}/billing-info");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test case to come');
  }
}
