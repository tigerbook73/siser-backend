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

    $this->listAssert(200, ['name' => 'user1.test'], 1);

    $this->listAssert(200, ['email' => 'user1.test@iifuture.com'], 1);

    $this->listAssert(200, ['full_name' => 'User1 Test']);

    $this->listAssert(200, ['phone_number' => '+61400000000']);

    $this->listAssert(200, ['country_code' => 'AU']);

    $this->listAssert(200, ['subscription_level' => '1']);

    $this->listAssert(200, ['seat_count' => '2']);

    $this->listAssert(200, ['name' => 'user1.test', 'email' => 'user1.test@iifuture.com'], 1);
  }

  public function testUserListError()
  {
    $this->listAssert(422, ['name' => ''])->assertJsonValidationErrors(['name' => 'The name field must have a value.']);

    $this->listAssert(422, ['email' => ''])->assertJsonValidationErrors(['email' => 'The email field must have a value.']);

    $this->listAssert(422, ['full_name' => ''])->assertJsonValidationErrors(['full_name' => 'The full name field must have a value.']);

    $this->listAssert(422, ['phone_number' => ''])->assertJsonValidationErrors(['phone_number' => 'The phone number field must have a value.']);

    $this->listAssert(422, ['country_code' => ''])->assertJsonValidationErrors(['country_code' => 'The country code field must have a value.']);

    $this->listAssert(422, ['subscription_level' => ''])->assertJsonValidationErrors(['subscription_level' => 'The subscription level field must have a value.']);

    $this->listAssert(422, ['seat_count' => ''])->assertJsonValidationErrors(['seat_count' => 'The seat count field must have a value.']);
  }
}
