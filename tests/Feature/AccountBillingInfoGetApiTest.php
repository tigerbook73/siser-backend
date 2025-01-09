<?php

namespace Tests\Feature;

class AccountBillingInfoGetApiTest extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testAccountBillingInfoGetOk()
  {
    $response = $this->getJson($this->baseUrl);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }
}
