<?php

namespace Tests\Feature;

class DesignPlanUpdateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanUpdateOk()
  {
    $this->updateAssert(200, $this->object->id);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
