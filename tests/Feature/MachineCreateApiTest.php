<?php

namespace Tests\Feature;

use App\Models\Machine;

class MachineCreateApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineCreateOk()
  {
    $this->createAssert();
  }

  // TODO: more tests to come
}
