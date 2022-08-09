<?php

namespace Tests\Feature;

class AdminUserUpdateApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserUpdateSuccess()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * success full name
     */
    $this->modelUpdate['full_name'] = 'Test Test';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['full_name'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    unset($this->modelUpdate['full_name']);
    $this->updateAssert(200, $this->object->id);

    /**
     * success roles
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['roles'] = array('admin');
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['roles'] = array('siser-backend');
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['roles'] = array('admin', 'siser-backend');
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['roles'] = array('siser-backend', 'admin');
    $this->updateAssert(200, $this->object->id);

    unset($this->modelUpdate['roles']);
    $this->updateAssert(200, $this->object->id);

    /**
     * success password
     */
    $this->modelUpdate = $modelUpdate;
    $newPassword = "!Aa1" . $this->createRandomString(28);
    $this->modelUpdate['password'] = $newPassword;
    $this->updateAssert(200, $this->object->id);
    $credentials = [
      'email' => $this->object->email,
      'password' => $newPassword,
    ];
    $this->assertTrue(!!auth('admin')->attempt($credentials));

    unset($this->modelUpdate['password']);
    $this->updateAssert(200, $this->object->id);
  }

  public function testAdminUserUpdateError()
  {
    /**
     * error name
     */
    $this->modelUpdate['name'] = 'Test Test';
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['name'] = $this->createRandomString(256);
    $this->updateAssert(400, $this->object->id);

    /**
     * error email
     */
    $this->modelUpdate['email'] = 'tester_manager@iifuture.com';
    $this->updateAssert(400, $this->object->id);

    /**
     * error full name
     */
    $this->modelUpdate['full_name'] = '';
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['full_name'] = $this->createRandomString(256);
    $this->updateAssert(400, $this->object->id);

    /**
     * error roles
     */
    $this->modelUpdate['roles'] = array('');
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['roles'] = array('siser-backend-iifuture');
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['roles'] = array('admin-iifuture', 'siser-backend');
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['roles'] = array('siser-backend', '');
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['roles'] = array('siser-backend', 'siser-backend');
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['roles'] = array('admin', 'siser-backend', 'global-admin');
    $this->updateAssert(400, $this->object->id);

    /**
     * error password
     */
    $this->modelUpdate['password'] = '';
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate['password'] = "!Aa1" . $this->createRandomString(29);
    $this->updateAssert(400, $this->object->id);
  }
}
