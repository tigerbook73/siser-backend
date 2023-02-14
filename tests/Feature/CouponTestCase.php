<?php

namespace Tests\Feature;

use App\Models\Coupon;
use Carbon\Carbon;
use Tests\ApiTestCase;
use Tests\Models\Coupon as ModelsCoupon;
use Tests\Models\Price;

class CouponTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/coupons';
  public string $model = Coupon::class;

  protected $hidden = [
    'start_date',
    'end_date'
  ];

  public Coupon $object;
  public Coupon $object2;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsCoupon);

    $this->modelCreate = [
      "code"                    => "coupon-create20",
      "description"             => "20% percent off",
      "condition" => [
        "new_customer_only"     => false,
        "new_subscription_only" => false,
        "upgrade_only"          => false
      ],
      "percentage_off"          => 20,
      "period"                  => 6,
      "start_date"              => Carbon::tomorrow(),
      "end_date"                => "2099-12-31"
    ];

    $this->modelUpdate = [
      "code"                    => "coupon-code20",
      "description"             => "20% percent off",
      "condition" => [
        "new_customer_only"     => false,
        "new_subscription_only" => false,
        "upgrade_only"          => false
      ],
      "percentage_off"          => 20,
      "period"                  => 6,
      "start_date"              => Carbon::today(),
      "end_date"                => "2099-12-31"
    ];

    $createData = $this->modelCreate;
    $createData['code']         = 'test-precreate-20';
    $createData['start_date']   = Carbon::today();
    $createData['status']       = 'active';
    $this->object = Coupon::create($createData);

    $createData = $this->modelCreate;
    $createData['code']         = 'test-precreate-30';
    $createData['start_date']   = Carbon::tomorrow();
    $createData['status']       = 'active';
    $this->object2 = Coupon::create($createData);
  }
}
