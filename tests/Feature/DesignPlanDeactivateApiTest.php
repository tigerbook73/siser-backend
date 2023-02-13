<?php

namespace Tests\Feature;

class DesignPlanDeactivateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanDeactivateOk()
  {
    $response = $this->postJson($this->baseUrl . '/' . $this->object->id . '/deactivate');
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
