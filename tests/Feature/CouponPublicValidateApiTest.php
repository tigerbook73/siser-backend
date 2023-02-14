<?php

namespace Tests\Feature;

use App\Models\Plan;

class CouponPublicValidateApiTest extends CouponTestCase
{
  public ?string $role = '';

  public function testCouponPublicValidateSuccess()
  {
    $response = $this->postJson('api/v1/coupon-validate', [
      'code' => $this->object->code,
      'plan_id' => Plan::public()->first()->id,
    ]);

    unset($this->modelSchema[array_search('status', $this->modelSchema)]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test case to come');
  }
}
