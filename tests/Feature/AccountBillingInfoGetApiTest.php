<?php

namespace Tests\Feature;

class AccountBillingInfoGetApiTest extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testAccountBillingInfoGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/billing-info");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
