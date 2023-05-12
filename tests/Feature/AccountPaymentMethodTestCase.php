<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Tests\DR\DrApiTestCase;
use Tests\DR\DrTestTrait;
use Tests\Models\PaymentMethod as ModelsPaymentMethod;


class AccountPaymentMethodTestCase extends DrApiTestCase
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
}
