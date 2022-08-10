<?php

namespace Tests\Feature;

class UserListApiTest extends UserTestCase
{
  public ?string $role = 'admin';

  public function testUserListSuccess()
  {
    $this->listAssert();

    $this->listAssert(200, ['name' => 'user1.test'], 1);

    $this->listAssert(200, ['email' => 'user1.test@iifuture.com'], 1);

    $this->listAssert(200, ['name' => 'user1.test', 'email' => 'user1.test@iifuture.com'], 1);

    $this->listAssert(200, ['name' => 'user1.testDoesNotExist']);

    $this->listAssert(200, ['name' => 'user1.testDoesNotExist', 'email' => 'user1.testDoesNotExist@iifuture.com']);

    /* Commented out below 2 test cases will be revisited later
    $this->listAssert(200, ['name' => '']);

    $this->listAssert(200, ['email' => '']);
    */
  }

  public function testUserListError()
  {
    $this->listAssert(400, ['full_name' => '']);

    $this->listAssert(400, ['full_name' => 'User1 Test']);

    $this->listAssert(400, ['full_name' => 'User1 Test', 'family_name' => 'Test']);
  }
}
