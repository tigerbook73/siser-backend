<?php

namespace Tests\Feature;

use App\Models\Plan;

class CouponCustomerValidateApiTest extends CouponTestCase
{
  public ?string $role = 'customer';

  public function testCouponCustomerValidateSuccess()
  {
    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    unset($this->modelSchema[array_search('status', $this->modelSchema)]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->couponInfoSchema);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
