<?php

namespace Tests\Feature;

class DesignPlanListApiTest extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanListSuccess()
  {
    $this->listAssert(200);

    $this->listAssert(200, ['name' => 'Leonardo® Design Studio Pro Monthly Plan']);

    $this->listAssert(200, ['product_name' => 'Leonardo® Design Studio Basic']);

    $this->listAssert(200, ['product_name' => 'Leonardo® Design Studio Software']);
  }
}
