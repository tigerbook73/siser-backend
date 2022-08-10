<?php

namespace Tests\Feature;

class AdminUserGetApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserGetSuccess()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testAdminUserGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);
  }
}
