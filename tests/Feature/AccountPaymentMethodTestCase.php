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

    $this->modelSchema = array_keys((array)new ModelsPaymentMethod);

    $this->modelCreate = [
      'type' => 'credit-card',
      // 'display_data'  => [
      //   'last_four_digits'  => '9999',
      //   'brand'             => 'visa',
      // ],
      'dr' => ['source_id' => 'digital-river-source-id-master'],
    ];

    $this->modelUpdate = [
      'type'          => 'credit-card',
      // 'display_data'  => [
      //   'last_four_digits'  => '8888',
      //   'brand'             => 'master',
      // ],
      'dr' => ['source_id'   => 'digital-river-source-id-visa'],
    ];
  }
}
