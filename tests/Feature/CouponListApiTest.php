<?php

namespace Tests\Feature;

class CouponListApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponListSuccess()
  {
    // TODO: mockup code issues
    $this->listAssert(200);

    $this->markTestIncomplete('more test case to come');
  }

  public function testCouponListError()
  {
    $this->markTestIncomplete('more test case to come');
  }
}
