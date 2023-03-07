<?php

namespace Tests\Feature;

use stdClass;

class AccountPaymentMethodGetApiTest extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/payment-method");
    $response->assertStatus(200)->assertJson([]);

    // create payment method
    $response = $this->postJson("{$this->baseUrl}/payment-method", $this->modelUpdate);

    $response = $this->getJson("{$this->baseUrl}/payment-method");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
