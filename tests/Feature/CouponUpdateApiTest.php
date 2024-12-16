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

  public function testCouponUpdateValidCountriesOk1()
  {
    $countries = ['CA', 'US'];
    $this->modelUpdate['condition']['countries'] = $countries;
    $this->updateAssert(200, $this->object->id);
  }

  protected function updateOKTest(array $countries)
  {
    $this->modelUpdate['condition']['countries'] = $countries;
    $this->noAssert = true;
    $response = $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['condition']['countries'] = collect($countries)->sort()->unique()->values()->toArray();
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson(array_diff_key($this->modelUpdate, array_flip($this->hidden)));

    return $response;
  }

  public function testCouponUpdateValidCountriesOk2()
  {
    $countries = ['US', 'CA'];
    $this->updateOKTest($countries);
  }

  public function testCouponUpdateValidCountriesOk3()
  {
    $countries = ['US', 'CA', 'CA'];
    $this->updateOKTest($countries);
  }

  public function testCouponUpdateInvalidCountriesNok()
  {
    $this->modelUpdate['condition']['countries'] = ['SU', 'CA'];
    $this->updateAssert(400, $this->object->id);
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
