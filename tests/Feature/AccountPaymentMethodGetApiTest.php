<?php

namespace Tests\Feature;

use stdClass;

class AccountPaymentMethodGetApiTest extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodGetOk()
  {
    $this->createBillingInfo();

    $response = $this->getJson("{$this->baseUrl}/payment-method");
    $response->assertStatus(200)->assertJson([]);

    /**
     * mock up functions
     */
    $this->mockAttachCustomerSource();

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
