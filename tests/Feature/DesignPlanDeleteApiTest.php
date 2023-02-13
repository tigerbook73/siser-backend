<?php

namespace Tests\Feature;

use App\Models\Base\DesignPlan;

class DesignPlanDeleteApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanDeleteOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->deleteJson("$this->baseUrl/" . $createResponse->json()['id']);
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
