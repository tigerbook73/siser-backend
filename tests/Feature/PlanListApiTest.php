<?php

namespace Tests\Feature;

class PlanListApiTest extends PlanTestCase
{
  public ?string $role = 'admin';

  public function testPlanListSuccess()
  {
    $this->listAssert();

    $this->listAssert(200, ['catagory' => 'machine']);

    $this->listAssert(200, ['catagory' => 'software']);

    $this->listAssert(200, []);

    $this->listAssert(200, ['catagory' => $this->object->catagory]);
  }

  public function testPlanListError()
  {
    $this->listAssert(422, ['catagory' => '']);

    $this->listAssert(422, ['catagory' => 'linux']);

    $this->listAssert(400, ['catagory' => 'machine', 'name' => 'LDS Machine Basic']);

    $this->listAssert(400, ['catagory' => 'machine', 'contract_term' => 'permanent']);

    $this->listAssert(400, ['contract_term' => 'year']);

    $this->listAssert(400, ['status' => 'active']);

    $this->listAssert(400, ['name' => 'LDS Machine Basic']);
  }
}
