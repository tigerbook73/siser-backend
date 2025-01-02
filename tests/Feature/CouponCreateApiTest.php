<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;

class CouponCreateApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  /**
   * paddle adapt TODO: ...
   */
  /*
  public function testCouponCreateOk()
  {
    $this->createAssert();
  }

  public function testCouponCreateLongtermOK()
  {
    $this->modelCreate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelCreate['interval_count'] = 0;
    $this->createAssert();
  }

  public function testCouponCreateLongtermInvalidIntervalCount()
  {
    $this->modelCreate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelCreate['interval_count'] = 1;
    $this->createAssert(400);
  }

  public function testCouponCreateLongtermFreeTrialNok()
  {
    $this->modelCreate['discount_type'] = Coupon::DISCOUNT_TYPE_FREE_TRIAL;
    $this->modelCreate['percentage_off'] = 100;
    $this->modelCreate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelCreate['interval_count'] = 0;
    $this->createAssert(400);
  }
  */
}
