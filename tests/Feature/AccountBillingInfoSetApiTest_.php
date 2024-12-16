<?php

namespace Tests\Feature;

class AccountBillingInfoSetApiTest_ extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testSetOk()
  {
    // update
    $response = $this->createOrUpdateBillingInfo($this->modelUpdate);

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

  public function testSetNokWithWrongLanguage()
  {
    // update
    $this->modelUpdate['language'] = 'fr';
    $response = $this->postJson('/api/v1/account/billing-info', $this->modelUpdate);
    $this->assertTrue($response->isClientError());

    $this->markTestIncomplete('more test cases to come');
  }

  public function testCustomerType()
  {
    $this->markTestIncomplete('test customer type');
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
