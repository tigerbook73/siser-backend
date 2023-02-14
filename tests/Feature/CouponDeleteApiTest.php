<?php

namespace Tests\Feature;

use App\Models\Base\Coupon;

class CouponDeleteApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponDeleteOk()
  {
    $createResponse = $this->createAssert();
    $response = $this->deleteJson("$this->baseUrl/" . $createResponse->json()['id']);
    $response->assertStatus(200);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
