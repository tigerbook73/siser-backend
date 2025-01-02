<?php

namespace Tests\Feature;

use App\Models\Coupon;

class CouponUpdateApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  /**
   * paddle adapt TODO: ...
   */
  /*
  public function testCouponUpdateOk()
  {
    $this->updateAssert(200, $this->object2->id);
  }

  public function testCouponUpdateLongtermOK()
  {
    $this->modelUpdate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelUpdate['interval_count'] = 0;
    $this->updateAssert(200, $this->object->id);
  }

  public function testCouponUpdateLongtermInvalidIntervalCount()
  {
    $this->modelUpdate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelUpdate['interval_count'] = 1;
    $this->updateAssert(400, $this->object->id);
  }

  public function testCouponUpdateLongtermFreeTrialNok()
  {
    $this->modelUpdate['discount_type'] = Coupon::DISCOUNT_TYPE_FREE_TRIAL;
    $this->modelUpdate['percentage_off'] = 100;
    $this->modelUpdate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelUpdate['interval_count'] = 0;
    $this->updateAssert(400, $this->object->id);
  }
  */
}
