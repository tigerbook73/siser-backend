<?php

namespace Tests\Feature;

use App\Models\Machine;

class MachineUpdateApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineUpdateOk()
  {
    $this->updateAssert(200, 1);
  }

  // TODO: more tests to come
}
