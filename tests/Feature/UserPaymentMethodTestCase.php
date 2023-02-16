<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Tests\ApiTestCase;
use Tests\Models\PaymentMethod as ModelsPaymentMethod;

class UserPaymentMethodTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/users';
  public string $model = PaymentMethod::class;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsPaymentMethod);
  }
}
