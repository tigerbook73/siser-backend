<?php

namespace Tests\Feature;

use App\Models\AdminUser;

class AdminUserUpdateApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserUpdateOk()
  {
    $this->updateAssert(200, 1);
  }

  // TODO: more tests to come
}
