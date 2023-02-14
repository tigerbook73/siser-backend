<?php

namespace Tests\Feature;

class DesignPlanListApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanListSuccess()
  {
    $this->listAssert(200);

    $this->listAssert(200, ['name' => 'LDS Premier Plan']);

    $this->listAssert(200, ['catagory' => 'machine']);

    $this->listAssert(200, ['catagory' => 'software']);

    $this->markTestIncomplete('more filter to do');
  }

  public function testDesignPlanListError()
  {
    $this->markTestIncomplete('more filter to do');
  }
}
