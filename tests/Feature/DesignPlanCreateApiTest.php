<?php

namespace Tests\Feature;

class DesignPlanCreateApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanCreateOk()
  {
    $this->createAssert();
  }
}
