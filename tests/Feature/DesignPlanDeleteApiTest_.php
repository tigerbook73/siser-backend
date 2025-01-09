<?php

namespace Tests\Feature;

class DesignPlanDeleteApiTest_ extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanDeleteOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->deleteJson("$this->baseUrl/" . $createResponse->json()['id']);
    $response->assertStatus(200);
  }
}
