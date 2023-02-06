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
      'price' => [
        'country',
        'currency',
        'price',
      ],
      'url',
      'status',
    ];

    $this->modelCreate = [];

    $this->modelUpdate = [];

    $this->object = Plan::first();
  }
}
