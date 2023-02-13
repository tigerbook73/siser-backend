<?php

namespace Tests\Feature;

class DesignPlanGetApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanGetSuccess()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testDesignPlanGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);

    $this->markTestIncomplete('mock code issues');
  }
}
