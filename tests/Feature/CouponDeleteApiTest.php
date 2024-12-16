<?php

namespace Tests\Feature;

use App\Models\Base\Coupon;

class CouponDeleteApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponDeleteOk()
  {
    // TODO: paddle adapt
    $this->markTestIncomplete('more test cases to come');

    $response = $this->deleteJson("$this->baseUrl/" . $this->object2->id);
    $response->assertStatus(200);
  }
}
