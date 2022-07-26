<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;
use App\Models\Plan;
use Tests\ApiTestCase;

class ConfigGeneralGetApiTest extends ConfigGeneralTestCase
{
  public ?string $role = 'admin';

  public function testConfigGeneralGetOk()
  {
    $response = $this->getJson($this->baseUrl);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }
}
