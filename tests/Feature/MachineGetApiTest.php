<?php

namespace Tests\Feature;

use App\Models\Machine;

class MachineGetApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineGetOk()
  {
    $this->getAssert(200, 1);
  }
}
