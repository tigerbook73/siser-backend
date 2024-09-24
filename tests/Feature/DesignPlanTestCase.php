<?php

namespace Tests\Feature;

use App\Models\Plan;
use Tests\ApiTestCase;
use Tests\Models\DesignPlan as ModelsDesignPlan;
use Tests\Models\Price;

class DesignPlanTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/design-plans';
  public string $model = Plan::class;

  public Plan $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsDesignPlan);
    $this->modelSchema['price_list'] = ['*' => array_keys((array)new Price)];

    $this->modelCreate = [
      'name' => 'LDS New-Test Plan',
      'product_name' => 'Leonardo® Design Studio Pro',
      'description' => 'test plan',
      'interval' => Plan::INTERVAL_MONTH,
      'subscription_level' => 2,
      'url' => '',
      'price_list' => [
        [
          'country' => 'US',
          'currency' => 'USD',
          'price' => 0.98,
        ]
      ]
    ];

    $this->modelUpdate = [
      // "name" => "LDS New-Test Plan",
      "description" => "updated plan",
      // "subscription_level" => 2,
      "url" => "",
      "price_list" => [
        [
          "country" => "US",
          "currency" => "USD",
          "price" => 1.98,
        ],
        [
          "country" => "AU",
          "currency" => "AUD",
          "price" => 2.98,
        ]
      ]
    ];

    $this->object = Plan::public()
      ->where('product_name', 'Leonardo® Design Studio Pro')
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->first();
  }
}
