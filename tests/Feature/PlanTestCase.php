<?php

namespace Tests\Feature;

use App\Models\Plan;
use Tests\ApiTestCase;

class PlanTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/plans';
  public string $model = Plan::class;


  public Plan $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      'id',
      'name',
      'catagory',
      'description',
      'subscription_level',
      'contract_term',
      'price' => [
        '*' => [
          'currency',
          'price',
        ]
      ],
      'auto_renew',
      'url',
      'status',
    ];

    $this->modelCreate = [];

    $this->modelUpdate = [];

    $this->object = Plan::first();
  }
}
