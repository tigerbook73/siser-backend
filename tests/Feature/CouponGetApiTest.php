<?php

namespace Tests\Feature;

class CouponGetApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponGetSuccess()
  {
    $this->getAssert(200, $this->object->id);
  }

  public function testCouponGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, 0);

    $this->markTestIncomplete('more test cases to come');
  }
}
