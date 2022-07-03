<?php

namespace Tests\Feature;

use App\Models\AdminUser;

class AdminUserListApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserListAll()
  {
    $this->listAssert();
  }
}
