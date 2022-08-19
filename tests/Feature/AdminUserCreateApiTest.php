<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

class AdminUserCreateApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  /**
   * Success test cases
   */

  /**
   * Success name
   */
  public function testAdminUserNameCreateSuccess()
  {
    $this->modelCreate['name'] = 'Test Test';
    $this->createAssert();
  }

  public function testAdminUserNameMaxLengthCreateSuccess()
  {
    $this->modelCreate['name'] = $this->createRandomString(255);
    $this->createAssert();
  }

  /**
   * Success email
   */
  public function testAdminUserEmailCreateSuccess()
  {
    $this->modelCreate['email'] = 'tester_manager@iifuture.com';
    $this->createAssert();
  }

  public function testAdminUserEmail2CreateSuccess()
  {
    $this->modelCreate['email'] = 'tester@iifuture';
    $this->createAssert();
  }

  /**
   * Success full name
   */
  public function testAdminUserFullNameCreateSuccess()
  {
    $this->modelCreate['full_name'] = 'Iifuture Tester Test';
    $this->createAssert();
  }

  public function testAdminUserFullNameMaxlengthCreateSuccess()
  {
    $this->modelCreate['full_name'] = $this->createRandomString(255);
    $this->createAssert();
  }

  /**
   * Success roles
   */
  public function testAdminUserRolesCreateSuccess()
  {
    DB::beginTransaction();
    $this->modelCreate['roles'] = ['admin'];
    $this->createAssert();
    DB::rollBack();

    DB::beginTransaction();
    $this->modelCreate['roles'] = ['siser-backend'];
    $this->createAssert();
    DB::rollBack();

    DB::beginTransaction();
    $this->modelCreate['roles'] = ['admin', 'siser-backend'];
    $this->createAssert();
    DB::rollBack();

    DB::beginTransaction();
    $this->modelCreate['roles'] = ['siser-backend', 'admin'];
    $this->createAssert();
    DB::rollBack();
  }

  /**
   * Success password
   */
  public function testAdminUserPasswordCreateSuccess()
  {
    DB::beginTransaction();
    $newPassword = 'abcd1234A!';
    $this->modelCreate['password'] = $newPassword;
    $this->createAssert();
    $credentials = [
      'email' => $this->modelCreate['email'],
      'password' => $newPassword,
    ];
    $this->assertTrue(!!auth('admin')->attempt($credentials));
    DB::rollBack();

    DB::beginTransaction();
    $newPassword = '!Aa1' . $this->createRandomString(28);
    $this->modelCreate['password'] = $newPassword;
    $this->createAssert();
    $credentials = [
      'email' => $this->modelCreate['email'],
      'password' => $newPassword,
    ];
    $this->assertTrue(!!auth('admin')->attempt($credentials));
    DB::rollBack();
  }

  /**
   * error test cases
   */

  /**
   * error name
   */
  public function testAdminUserNoNameCreateError()
  {
    unset($this->modelCreate['name']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.name', ['The name field is required.']);
  }

  public function testAdminUserEmptyNameCreateError()
  {
    $this->modelCreate['name'] = '';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.name', ['The name field is required.']);
  }

  public function testAdminUserNameExceedsLengthCreateError()
  {
    $this->modelCreate['name'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.name', ['The name must not be greater than 255 characters.']);
  }

  /**
   * error email
   */
  public function testAdminUserNoEmailCreateError()
  {
    unset($this->modelCreate['email']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email field is required.']);
  }

  public function testAdminUserEmptyEmailCreateError()
  {
    $this->modelCreate['email'] = '';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email field is required.']);
  }

  public function testAdminUserInvalidFormatEmailCreateError()
  {
    $this->modelCreate['email'] = 'tester';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email must be a valid email address.']);
  }

  public function testAdminUserEmailExceedLengthCreateError()
  {
    $this->modelCreate['email'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email must be a valid email address.', 'The email must not be greater than 255 characters.']);
  }

  public function testAdminUserEmailExistedCreateError()
  {
    $this->modelCreate['email'] = 'admin@iifuture.com';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email has already been taken.']);
  }

  public function testAdminUserEmailExceedsLengthCreateError()
  {
    $this->modelCreate['email'] = 'a@' . $this->createRandomString(250) . '.com';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.email', ['The email must be a valid email address.', 'The email must not be greater than 255 characters.']);
  }

  /**
   * error full name
   */
  public function testAdminUserNoFullNameCreateError()
  {
    unset($this->modelCreate['full_name']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.full_name', ['The full name field is required.']);
  }

  public function testAdminUserEmptyFullNameCreateError()
  {
    $this->modelCreate['full_name'] = '';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.full_name', ['The full name field is required.']);
  }

  public function testAdminUserFullNameExceedsMaxLengthCreateError()
  {
    $this->modelCreate['full_name'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.full_name', ['The full name must not be greater than 255 characters.']);
  }

  /**
   * error roles
   */
  public function testAdminUserRolesCreateError()
  {

    $this->modelCreate['roles'] = [''];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.0' => 'The roles.0 field is required.']);

    $this->modelCreate['roles'] = ['siser-backend-iifuture'];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.0' => 'The selected roles.0 is invalid.']);

    $this->modelCreate['roles'] = ['admin-iifuture', 'siser-backend'];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.0' => 'invalid']);

    $this->modelCreate['roles'] = ['siser-backend', ''];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.1' => 'required']);

    $this->modelCreate['roles'] = ['siser-backend', 'siser-backend'];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.0' => 'duplicate']);

    $this->modelCreate['roles'] = ['admin', 'siser-backend', 'global-admin'];
    $response = $this->createAssert(422);
    $response->assertJsonValidationErrors(['roles.2' => 'invalid']);
  }

  /**
   * error password
   */
  public function testAdminUserNoPasswordCreateError()
  {
    unset($this->modelCreate['password']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.password', ['The password field is required.']);
  }

  public function testAdminUserEmptyPasswordCreateError()
  {
    $this->modelCreate['password'] = '';
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.password', ['The password field is required.']);
  }

  public function testAdminUserPasswordExceedsMaxLengthCreateError()
  {
    $this->modelCreate['password'] = "!Aa1" . $this->createRandomString(29);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.password', ['The password must not be greater than 32 characters.']);
  }
}
