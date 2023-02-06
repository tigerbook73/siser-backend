<?php

namespace Tests\Feature;

class PlanListApiTest extends PlanTestCase
{
  public ?string $role = 'admin';

  public function testPlanListSuccess()
  {
    // TODO: mockup code issues
    $this->markTestIncomplete('mock code issues');

    $this->listAssert();

    $this->listAssert(200, ['name' => 'LDS Machine Basic']);

    $this->listAssert(200, ['catagory' => 'machine']);

    $this->listAssert(200, ['catagory' => 'software']);

    $this->listAssert(200, []);

    $this->listAssert(200, ['catagory' => $this->object->catagory]);

    $this->listAssert(200, ['status' => 'active']);

    $this->markTestIncomplete('more filter to do');
  }

  public function testPlanListError()
  {
    // TODO: mockup code issue
    // $response = $this->listAssert(422, ['catagory' => '']);
    // $response->assertJsonValidationErrors(['catagory' => 'The catagory field must have a value.']);

    // TODO: mockup code issue
    // $response = $this->listAssert(422, ['catagory' => 'linux']);
    // $response->assertJsonValidationErrors(['catagory' => 'The selected catagory is invalid.']);

    $this->markTestIncomplete('more filter to do');
  }
}
