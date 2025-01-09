<?php

namespace Tests\Feature;

class AccountPaymentMethodGetApiTest_ extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/payment-method");
    $response->assertStatus(200)->assertJson([]);
    return $response;
  }
}
