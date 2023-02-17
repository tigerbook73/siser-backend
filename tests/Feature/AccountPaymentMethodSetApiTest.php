<?php

namespace Tests\Feature;

class AccountPaymentMethodSetApiTest extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodSetOk()
  {
    // update
    $response = $this->postJson("{$this->baseUrl}/payment-method", $this->modelUpdate);
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
