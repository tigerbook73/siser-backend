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
    $this->createAssert(422);
  }

  public function testAdminUserEmptyNameCreateError()
  {
    $this->modelCreate['name'] = '';
    $this->createAssert(422);
  }

  public function testAdminUserNameExceedsLengthCreateError()
  {
    $this->modelCreate['name'] = $this->createRandomString(256);
    $this->createAssert(422);
  }

  /**
   * error email
   */
  public function testAdminUserNoEmailCreateError()
  {
    unset($this->modelCreate['email']);
    $this->createAssert(422);
  }

  public function testAdminUserEmptyEmailCreateError()
  {
    $this->modelCreate['email'] = '';
    $this->createAssert(422);
  }

  public function testAdminUserInvalidFormatEmailCreateError()
  {
    $this->modelCreate['email'] = 'tester';
    $this->createAssert(422);
  }

  public function testAdminUserEmailExceedLengthCreateError()
  {
    $this->modelCreate['email'] = $this->createRandomString(256);
    $this->createAssert(422);
  }

  public function testAdminUserEmailExistedCreateError()
  {
    $this->modelCreate['email'] = 'admin@iifuture.com';
    $this->createAssert(422);
  }

  public function testAdminUserEmailExceedsLengthCreateError()
  {
    $this->modelCreate['email'] = 'a@' . $this->createRandomString(250) . '.com';
    $this->createAssert(422);
  }

  /**
   * error full name
   */
  public function testAdminUserNoFullNameCreateError()
  {
    unset($this->modelCreate['full_name']);
    $this->createAssert(422);
  }

  public function testAdminUserEmptyFullNameCreateError()
  {
    $this->modelCreate['full_name'] = '';
    $this->createAssert(422);
  }

  public function testAdminUserFullNameExceedsMaxLengthCreateError()
  {
    $this->modelCreate['full_name'] = $this->createRandomString(256);
    $this->createAssert(422);
  }

  /**
   * error roles
   */
  public function testAdminUserRolesCreateError()
  {

    $this->modelCreate['roles'] = [''];
    $this->createAssert(422);

    $this->modelCreate['roles'] = ['siser-backend-iifuture'];
    $this->createAssert(422);

    $this->modelCreate['roles'] = ['admin-iifuture', 'siser-backend'];
    $this->createAssert(422);

    $this->modelCreate['roles'] = ['siser-backend', ''];
    $this->createAssert(422);

    $this->modelCreate['roles'] = ['siser-backend', 'siser-backend'];
    $this->createAssert(422);

    $this->modelCreate['roles'] = ['admin', 'siser-backend', 'global-admin'];
    $this->createAssert(422);
  }

  /**
   * error password
   */
  public function testAdminUserNoPasswordCreateError()
  {
    unset($this->modelCreate['password']);
    $this->createAssert(422);
  }

  public function testAdminUserEmptyPasswordCreateError()
  {
    $this->modelCreate['password'] = '';
    $this->createAssert(422);
  }

  public function testAdminUserPasswordExceedsMaxLengthCreateError()
  {
    $this->modelCreate['password'] = "!Aa1" . $this->createRandomString(29);
    $this->createAssert(422);
  }
}
