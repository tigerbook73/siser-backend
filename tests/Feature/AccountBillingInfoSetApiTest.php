<?php

namespace Tests\Feature;

class AccountBillingInfoSetApiTest extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testAccountBillingInfoSetOk()
  {
    // update
    $response = $this->postJson("{$this->baseUrl}/billing-info", $this->modelUpdate);
    $response->assertSuccessful()
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    // get
    $response = $this->getJson("{$this->baseUrl}/billing-info");
    $response->assertSuccessful()
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
