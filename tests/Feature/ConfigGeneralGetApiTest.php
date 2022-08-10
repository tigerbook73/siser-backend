<?php

namespace Tests\Feature;

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
