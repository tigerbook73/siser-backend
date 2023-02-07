<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;
use App\Models\Plan;
use stdClass;
use Tests\ApiTestCase;
use Tests\Models\GeneralConfiguration as ModelsGeneralConfiguration;

class ConfigGeneralTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/config/general';
  public string $model = GeneralConfiguration::class;

  public stdClass $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsGeneralConfiguration);

    $this->modelCreate = [];

    $this->modelUpdate = [
      "machine_license_unit" => 9,
    ];

    $this->object = (object)GeneralConfiguration::getAll();
  }
}
