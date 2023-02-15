<?php

namespace Tests\Feature;

class AccountBillingInfoSetApiTest extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testAccountBillingInfoSetOk()
  {
    // update
    $response = $this->postJson("{$this->baseUrl}/billing-info", $this->modelUpdate);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    // get
    $response = $this->getJson("{$this->baseUrl}/billing-info");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test case to come');
  }
}
