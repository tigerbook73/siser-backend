<?php

namespace Tests\Feature;

use App\Models\User;

class UserGetApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserGetOk()
  {
    $this->getAssert(200, $this->object->id);
  }
}
