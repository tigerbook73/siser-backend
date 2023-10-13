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

  public function testDesignPlanDeactivateMonthPlanFailedOk()
  {
    // monthly plan can not be deactivated if referenced by a annual plan
    $response = $this->postJson($this->baseUrl . '/' . $this->object->id . '/deactivate');
    $response->assertStatus(400);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
