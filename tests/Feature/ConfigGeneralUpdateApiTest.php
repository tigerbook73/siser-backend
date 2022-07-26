<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;

class ConfigGeneralUpdateApiTest extends ConfigGeneralTestCase
{
  public ?string $role = 'admin';

  public function testConfigGeneralUpdateOk()
  {
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }
}
