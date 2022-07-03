<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;

class AccountMachineListApiTest extends MachineTestCase
{
  public string $baseUrl = '/api/v1/account/machines';
  public ?string $role = 'customer';

  public function testAccountMachineListAll()
  {
    $this->listWithForeignAssert(200, [User::class => $this->user->id], false);
  }
}
