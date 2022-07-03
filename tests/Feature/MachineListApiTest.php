<?php

namespace Tests\Feature;

use App\Models\Machine;

class MachineListApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineListAll()
  {
    $this->listAssert();
  }

  public function testMachineListFilter()
  {
    // 
    $this->listAssert(
      200,
      ['serial_no' => $this->object->serial_no],
    );

    // 
    $this->listAssert(
      200,
      ['user_id' => 1],
    );
  }

  public function testMachineListNone()
  {
    // 
    $this->listAssert(
      200,
      ['serial_no' => "99999999"],
    );

    // 
    $this->listAssert(
      200,
      ['user_id' => "99999999"],
      0
    );
  }
}
