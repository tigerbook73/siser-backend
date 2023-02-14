<?php

namespace Tests\Feature;

class CouponUpdateApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponUpdateOk()
  {
    $this->updateAssert(200, $this->object->id);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
