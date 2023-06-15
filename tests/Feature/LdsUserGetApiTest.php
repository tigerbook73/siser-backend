<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;
use Tests\Models\LdsLicense as ModelsLdsLicense;

class LdsUserGetApiTest extends ApiTestCase
{
  public ?string $role = 'admin';
  public string $baseUrl = '/api/v1/users';

  protected function setUp(): void
  {
    parent::setUp();
    $this->modelSchema = array_keys((array)new ModelsLdsLicense);
  }

  public function testGetOk()
  {
    $user = User::first();
    $response = $this->getJson("{$this->baseUrl}/{$user->id}/lds-license");
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    return $response;
  }
}
