<?php

namespace Tests\Feature;

class UserFullGetApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserFullGetSuccess()
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

  public function testUserFullGetError()
  {
    $userFullSchema = $this->modelSchema;
    $userFullSchema['machines'] = [
      '*' => $this->machineSchema,
    ];
    $userFullSchema['subscriptions'] = [
      '*' => $this->subscriptionSchema,
    ];

    $response = $this->getJson("{$this->baseUrl}/999999999999999999/full");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}/-1/full");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}/0/full");
    $response->assertStatus(404);

    $response = $this->getJson("{$this->baseUrl}//full");
    $response->assertStatus(404);
  }
}
