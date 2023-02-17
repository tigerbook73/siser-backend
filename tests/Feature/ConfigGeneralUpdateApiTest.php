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
    $response->assertJsonValidationErrors(['machine_license_unit' => 'The machine license unit must be between 1 and 10.']);

    $this->modelUpdate['machine_license_unit'] = 11;
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['machine_license_unit' => 'The machine license unit must be between 1 and 10.']);

    $this->modelUpdate['machine_license_unit'] = 'x';
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['machine_license_unit' => 'The machine license unit must be an integer.']);

    $this->modelUpdate['machine_license_unit'] = '';
    $response = $this->patchJson($this->baseUrl, $this->modelUpdate);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['machine_license_unit' => 'The machine license unit field must have a value.']);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
