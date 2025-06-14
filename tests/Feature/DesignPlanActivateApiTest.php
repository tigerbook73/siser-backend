<?php

namespace Tests\Feature;

use App\Models\Plan;

class DesignPlanActivateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanActivatateOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json()['id'] . '/activate');
    $response->assertStatus(200)
      ->assertJson(['status' => Plan::STATUS_ACTIVE]);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
