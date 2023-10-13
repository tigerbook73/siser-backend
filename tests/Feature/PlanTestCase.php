<?php

namespace Tests\Feature;

use App\Models\Plan;
use Tests\ApiTestCase;
use Tests\Models\Plan as ModelsPlan;
use Tests\Models\Price as ModelsPrice;

class PlanTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/plans';
  public string $model = Plan::class;


  public Plan $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsPlan);
    $this->modelSchema[] = 'next_plan_info';
    $this->modelSchema['price'] = array_keys((array)new ModelsPrice);
    $this->modelSchema = array_filter($this->modelSchema, fn ($value) => $value !== 'next_plan');
    unset($this->modelSchema['price_list']);

    $this->modelCreate = [];

    $this->modelUpdate = [];

    $this->object = Plan::public()->first();
  }
}
