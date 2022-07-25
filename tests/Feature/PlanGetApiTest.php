<?php

namespace Tests\Feature;

use App\Models\Plan;

class PlanGetApiTest extends PlanTestCase
{
  public ?string $role = 'admin';

  public function testPlanGetOk()
  {
    $this->getAssert(200, 1);
  }
}
