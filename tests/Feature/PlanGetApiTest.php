<?php

namespace Tests\Feature;

class PlanGetApiTest extends PlanTestCase
{
  public ?string $role = '';

  public function testPlanGetSuccess()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testPlanGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);
  }
}
