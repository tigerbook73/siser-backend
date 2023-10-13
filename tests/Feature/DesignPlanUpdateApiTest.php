<?php

namespace Tests\Feature;

use App\Models\Plan;

class DesignPlanUpdateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanUpdateOk()
  {
    $createResponse = $this->createAssert();
    $this->updateAssert(200, $createResponse->json('id'));
  }

  public function testDesignPlanUpdateMonthlyPlanFailed()
  {
    $monthPlan = $this->object = Plan::public()
      ->where('product_name', 'Leonardo™ Design Studio Pro')
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->first();

    // annual plan conflict with modelUpdate
    $this->updateAssert(400, $monthPlan->id);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
