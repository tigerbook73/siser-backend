<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Tests\ApiTestCase;
use Tests\Models\PaymentMethod as ModelsPaymentMethod;

class AccountPaymentMethodTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/account';
  public string $model = PaymentMethod::class;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_diff(array_keys((array)new ModelsPaymentMethod), ['id']);
    $this->modelCreate = [
      'type' => 'creditCard',
      // 'display_data'  => [
      //   'last_four_digits'  => '9999',
      //   'brand'             => 'visa',
      // ],
      'dr' => ['source_id' => 'digital-river-source-id-master'],
    ];

    $this->modelUpdate = [
      'type'          => 'creditCard',
      // 'display_data'  => [
      //   'last_four_digits'  => '8888',
      //   'brand'             => 'master',
      // ],
      'dr' => ['source_id'   => 'digital-river-source-id-visa'],
    ];
  }

  public function createBillingInfo()
  {
    $response = $this->postJson('/api/v1/account/billing-info', [
      'first_name'    => 'first_name',
      'last_name'     => 'last_name',
      'phone'         => '',
      'organization'  => '',
      'email'         => 'test-case@me.com',
      'address' => [
        'line1'       => '328 Reserve Road,  VIC',
        'line2'       => '',
        'city'        => 'Cheltenham',
        'postcode'    => '3192',
        'state'       => 'VIC',
        'country'     => 'AU',
      ]
    ]);

    // refresh authenticated user data
    $this->user->refresh();

    return $response;
  }
}
