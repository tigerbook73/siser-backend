<?php

namespace Tests\Feature;

class MachineDeleteApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineDeleteOk()
  {
    $response = $this->deleteJson("$this->baseUrl/" . $this->object->id);
    $response->assertStatus(200);

    $user = $this->object->user;
    $this->assertTrue($user->subscription_level == 0);
    $this->assertTrue($user->license_count == 0);
  }

  public function testMachineDeleteNok()
  {
    $response = $this->deleteJson("$this->baseUrl/" . 999999);
    $response->assertStatus(404);
  }
}
