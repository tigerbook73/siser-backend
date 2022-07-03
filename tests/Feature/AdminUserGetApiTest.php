<?php

namespace Tests\Feature;

use App\Models\AdminUser;

class AdminUserGetApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserGetOk()
  {
    $this->getAssert(200, 1);
  }
}
