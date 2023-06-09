<?php

namespace Tests\Feature;

use App\Models\Base\PaymentMethod;
use App\Models\User;

class UserPaymentMethodGetApiTest extends UserPaymentMethodTestCase
{
  public ?string $role = 'admin';

  public function testUserPaymentMethodGetOk()
  {
    $user = User::first();

    // no payment method
    $response = $this->getJson("{$this->baseUrl}/{$user->id}/payment-method");
    $response->assertStatus(200)->assertJson([]);


    // created payment method
    $paymentMethodCreate = [
      'user_id'       => $user->id,
      'type'          => 'creditCard',
      'display_data'  => [
        'brand'             => 'visa',
        'last_four_digits'  => '9999',
        'expiration_year'   => 2099,
        'expiration_month'  => 7,
      ],
      'dr' => [
        'source_id' => 'digital-river-source-id-visa'
      ],
    ];
    PaymentMethod::create($paymentMethodCreate);
    $response = $this->getJson("{$this->baseUrl}/{$user->id}/payment-method");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($paymentMethodCreate);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
