<?php

namespace Tests\Feature;

class ConfigGeneralUpdateApiTest extends ConfigGeneralTestCase
{
  public ?string $role = 'admin';

  public function testConfigGeneralUpdateSuccess()
  {
    $this->modelUpdate['machine_license_unit'] = 1;
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    $this->modelUpdate['machine_license_unit'] = 10;
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);
  }

  public function testConfigGeneralUpdateError()
  {
    $this->modelUpdate['machine_license_unit'] = 0;
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);

    $this->modelUpdate['machine_license_unit'] = 11;
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);

    $this->modelUpdate['machine_license_unit'] = 'x';
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);

    $this->modelUpdate['machine_license_unit'] = '';
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);
  }
}
