<?php

namespace Tests\Feature;

use App\Models\User;

class CouponCreateApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponCreateOk()
  {
    $this->createAssert();
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
