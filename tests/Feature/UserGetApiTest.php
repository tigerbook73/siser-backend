<?php

namespace Tests\Feature;

class UserGetApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserGetSuccess()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testUserGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);
  }
}
