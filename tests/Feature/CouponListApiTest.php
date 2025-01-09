<?php

namespace Tests\Feature;

class CouponListApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponListSuccess()
  {
    $this->listAssert(200);
  }
}
