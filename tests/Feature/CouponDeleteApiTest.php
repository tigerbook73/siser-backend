<?php

namespace Tests\Feature;

use App\Models\Base\Coupon;

class CouponDeleteApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponDeleteOk()
  {
    $response = $this->deleteJson("$this->baseUrl/" . $this->object2->id);
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
