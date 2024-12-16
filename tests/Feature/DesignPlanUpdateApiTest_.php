<?php

namespace Tests\Feature;

use App\Models\Plan;

class DesignPlanUpdateApiTest_ extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanUpdateOk()
  {
    $createResponse = $this->createAssert();
    $this->updateAssert(200, $createResponse->json('id'));
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
