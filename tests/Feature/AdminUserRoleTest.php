<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

class AdminUserRoleTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  /**
   * Success test cases
   */

  /**
   * Success name
   */
  public function testAdminUserRoleTest1()
  {

    $this->markTestIncomplete('can user with different roles access different resource');
    /**
     * e.g. siser-backend can not create users, support can not create machines
     */
  }
}
