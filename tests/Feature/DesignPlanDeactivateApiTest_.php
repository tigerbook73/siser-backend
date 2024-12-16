<?php

namespace Tests\Feature;

use App\Models\Plan;

class DesignPlanDeactivateApiTest_ extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanDeactivateOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json('id') . '/activate');
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json('id') . '/deactivate');
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
