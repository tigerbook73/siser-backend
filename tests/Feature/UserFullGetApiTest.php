<?php

namespace Tests\Feature;

use App\Models\User;

class UserFullGetApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserFullGetOk()
  {
    $userFullSchema = $this->modelSchema;
    $userFullSchema['machines'] = [
      '*' => $this->machineSchema,
    ];
    $userFullSchema['subscriptions'] = [
      '*' => $this->subscriptionSchema,
    ];

    $response = $this->getJson("{$this->baseUrl}/{$this->object->id}/full");
    $response->assertStatus(200)
      ->assertJsonStructure($userFullSchema);

    return $response;
  }

  // TODO: more tests to come
}
