<?php

namespace Tests\Feature;

class MachineGetApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineGetOk()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testMachineGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);
  }
}
