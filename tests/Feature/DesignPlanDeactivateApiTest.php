<?php

namespace Tests\Feature;

use App\Models\Plan;

class DesignPlanDeactivateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanDeactivateOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json('id') . '/activate');
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json('id') . '/deactivate');
    $response->assertStatus(200);
  }
}
