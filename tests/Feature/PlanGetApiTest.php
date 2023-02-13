<?php

namespace Tests\Feature;

class PlanGetApiTest extends PlanTestCase
{
  public ?string $role = 'customer';

  public function testPlanGetSuccess()
  {
    $this->getAssert(200, $this->object->id, ['country' => $this->object->price_list[0]['country']]);
  }

  public function testPlanGetError()
  {
    // TODO: mockup code issues
    $this->markTestIncomplete('mock code issues');

    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);
  }
}
