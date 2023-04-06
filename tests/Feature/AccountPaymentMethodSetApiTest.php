<?php

namespace Tests\Feature;

class AccountPaymentMethodSetApiTest extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodSetOk()
  {
    $this->createBillingInfo();

    // update
    $response = $this->postJson("{$this->baseUrl}/payment-method", $this->modelUpdate);
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
