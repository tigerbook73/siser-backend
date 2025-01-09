<?php

namespace Tests\Feature;

class DesignPlanCreateApiTest_ extends DesignPlanTestCase
{
  public ?string $role = 'admin';

  public function testDesignPlanCreateOk()
  {
    $this->createAssert();
  }
}
