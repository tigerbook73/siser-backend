<?php

namespace Tests\Feature;

use App\Models\User;

class UserListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserListAll()
  {
    $this->listAssert();
  }
}
