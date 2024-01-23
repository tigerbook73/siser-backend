<?php

namespace Tests\Feature;

use App\Models\Plan;

class CouponPublicValidateApiTest extends CouponTestCase
{
  public ?string $role = 'customer';

  public function testCouponValidateOk()
  {
    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

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

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
