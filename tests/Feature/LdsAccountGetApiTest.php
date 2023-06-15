<?php

namespace Tests\Feature;

use Tests\Models\LdsLicense as ModelsLdsLicense;

class LdsAccountGetApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  protected function setUp(): void
  {
    parent::setUp();
    $this->modelSchema = array_keys((array)new ModelsLdsLicense);
  }

  public function testGetOk()
  {
    $response = $this->getJson("{$this->baseUrl}/lds-license");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }
}
