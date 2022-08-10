<?php

namespace Tests\Feature;

class AdminUserListApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserListSuccess()
  {
    $this->listAssert();

    $this->listAssert(200, ['email' => 'admin@iifuture.com'], 1);

    $this->listAssert(200, ['email' => 'adminNotExists@iifuture.com'], 0);

    $this->listAssert(200, ['name' => 'admin'], 1);

    $this->listAssert(200, ['email' => 'admin@iifuture.com', 'name' => 'admin'], 1);

    $this->listAssert(200, ['email' => 'adminNotExists@iifuture.com', 'name' => 'admin'], 0);

    /* Note: Commented out as this case will be revisited later
    $this->listAssert(200, ['email' => ''], 0);
    */
  }
}
