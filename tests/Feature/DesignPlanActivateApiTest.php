<?php

namespace Tests\Feature;

class DesignPlanActivateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanActivatateOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->postJson($this->baseUrl . '/' . $createResponse->json()['id'] . '/activate');
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
