<?php

namespace Tests\Feature;

use App\Models\Plan;

class PlanListApiTest extends PlanTestCase
{
  public ?string $role = 'admin';

  public function testPlanListAll()
  {
    $this->listAssert();
  }

  public function testPlanListFilter()
  {
    // 
    $this->listAssert(
      200,
      ['catagory' => $this->object->catagory],
    );
  }
}
