<?php

namespace Tests\Feature;

class AccountFullGetApiTest extends AccountTestCase
{
  public ?string $role = 'customer';

  public function testUserFullGetOk()
  {
    $userFullSchema = $this->modelSchema;
    $userFullSchema['machines'] = [
      '*' => $this->machineSchema,
    ];
    $userFullSchema['subscriptions'] = [
      '*' => $this->subscriptionSchema,
    ];

    $response = $this->getJson("{$this->baseUrl}/full");
    $response->assertStatus(200)
      ->assertJsonStructure($userFullSchema);

    return $response;
  }
}
