<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;
use App\Models\Plan;
use stdClass;
use Tests\ApiTestCase;

class ConfigGeneralTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/config/general';
  public string $model = GeneralConfiguration::class;

  public stdClass $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      "machine_license_unit",
    ];

    $this->modelCreate = [];

    $this->modelUpdate = [
      "machine_license_unit" => 9,
    ];

    $this->object = (object)GeneralConfiguration::getAll();
  }
}
