<?php

namespace Tests\Feature;

class MachineListApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineListAll()
  {
    $this->listAssert();
  }

  public function testMachineListSuccess()
  {
    $this->listAssert(200, ['serial_no' => $this->object->serial_no]);

    $this->listAssert(200, ['serial_no' => '9999999999']);

    /* Note: Commented out as this case will be revisited later
    $this->listAssert(200, ['serial_no' => '']);
    */

    $this->listAssert(200, ['user_id' => $this->object->id]);

    $this->listAssert(200, ['user_id' => '9999999999'], 0);
  }

  public function testMachineListError()
  {
    $response = $this->listAssert(422, ['user_id' => '']);
    $response->assertJsonPath('errors.user_id', ['The user id must be an integer.']);

    $response = $this->listAssert(422, ['user_id' => 'x']);
    $response->assertJsonPath('errors.user_id', ['The user id must be an integer.']);
  }
}
