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
    $response->assertStatus(404);


    // created payment method
    $paymentMethodCreate = [
      'user_id'       => $user->id,
      'type'          => 'credit-card',
      'display_data'  => [
        'last_four_digits'  => '9999',
        'brand'             => 'visa',
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
