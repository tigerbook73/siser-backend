<?php

namespace Tests\Feature;

use App\Models\AdminUser;

class AdminUserCreateApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserCreateOk()
  {
    $this->createAssert();
  }

  // TODO: more tests to come
}
