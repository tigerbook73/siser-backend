<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;

class CouponCustomerValidateApiTest extends CouponTestCase
{
  public ?string $role = 'customer';

  public function testCouponValidateOk()
  {
    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    // response does not contain status
    unset($this->modelSchema[array_search('status', $this->modelSchema)]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->couponInfoSchema);
  }

  public function testCouponValidateCountryOk()
  {
    // update coupon with countries
    $this->modelUpdate['condition']['countries'] = [$this->user->billing_info->address['country']];
    $this->noAssert = true;
    $this->updateAssert(200, $this->object->id);

    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    // response does not contain status
    unset($this->modelSchema[array_search('status', $this->modelSchema)]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->couponInfoSchema);
  }

  public function testCouponValidateCountryNok()
  {
    // update coupon with countries
    $this->modelUpdate['condition']['countries'] = ['ZA'];
    $this->modelUpdate['status'] = 'active';
    $this->noAssert = true;
    $this->actingAsAdmin();
    $this->updateAssert(200, $this->object->id);
    $this->object->refresh();
    $this->actingAsDefault();

    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    $response->assertStatus(400);
  }

  public function testCouponValidateLongtermOk()
  {
    // update coupon with longterm period
    $this->modelUpdate['interval'] = Coupon::INTERVAL_LONGTERM;
    $this->modelUpdate['interval_count'] = 0;
    $this->noAssert = true;
    $this->updateAssert(200, $this->object->id);

    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    // response does not contain status
    unset($this->modelSchema[array_search('status', $this->modelSchema)]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->couponInfoSchema);
  }

  public function testCouponValidateFreeTrialTwice()
  {
    $plan = Plan::public()->first();
    $coupon = Coupon::where('discount_type', Coupon::DISCOUNT_TYPE_FREE_TRIAL)->first();

    // fake a subscription with the longterm coupon
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($this->user->billing_info)
      ->fillPlanAndCoupon($plan, $coupon)
      ->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->start_date = now();
    $subscription->save();

    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $coupon->code,
      'plan_id' => $plan->id,
    ]);

    // free coupon can not be redeemed twice by the same user twice
    $response->assertStatus(400);
  }
}
