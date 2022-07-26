<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;
use App\Models\Plan;
use Tests\ApiTestCase;

class ConfigGeneralTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/config/general';
  public string $model = GeneralConfiguration::class;

  public GeneralConfiguration $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      "machine_license_unit",
    ];

    $this->modelCreate = [];

    $this->modelUpdate = [
      "machine_license_unit" => 10,
    ];

    $this->object = GeneralConfiguration::first();
  }
}
