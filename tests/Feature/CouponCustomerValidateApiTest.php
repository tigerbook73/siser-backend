<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Invoice;
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

    $this->user->getActiveSubscription()?->stop(Subscription::STATUS_STOPPED, 'test');

    // fake a subscription with the longterm coupon
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($this->user->billing_info)
      ->fillPlanAndCoupon($plan, $coupon)
      ->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->start_date = now();
    $subscription->save();

    $invoice = (new Invoice(
      [
        'user_id' => $subscription->user_id,
        'plan_id' => $subscription->plan_id,
        'subscription_id' => $subscription->id,
        'coupon_id' => $subscription->coupon_id,
        'billing_info' => $subscription->billing_info,
        'plan_info' => $subscription->plan_info,
        'coupon_info' => $subscription->coupon_info,
        'license_package_info' => $subscription->license_package_info,
        'period' => 1,
        'currency' => $subscription->currency,
        'items' => [], //
        'payment_method_info' => $subscription->payment_method_info,
        'subtotal' => 10.0,
        'discount' => 1.0,
        'tax_rate' => 0.1,
        'total_tax' => 0.9,
        'total_amount' => 9.9,
        'status' => Invoice::STATUS_COMPLETED,
        'dr' => [],
        'created_at' => now(),
        'updated_at' => now(),
      ]
    ))->save();


    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $coupon->code,
      'plan_id' => $plan->id,
    ]);

    // free coupon can not be redeemed twice by the same user twice
    $response->assertStatus(400);
  }
}
