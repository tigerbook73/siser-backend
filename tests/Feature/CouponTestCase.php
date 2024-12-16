<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use Carbon\Carbon;
use Tests\ApiTestCase;
use Tests\Models\Coupon as ModelsCoupon;
use Tests\Models\CouponInfo;

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

  public $couponInfoSchema = [];

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsCoupon);
    $this->couponInfoSchema = array_keys((array)new CouponInfo);

    $coupon_event = '2023';

    $this->modelCreate = [
      'coupon_event'            => $coupon_event,
      'discount_type'           => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'type'                    => Coupon::TYPE_SHARED,
      'code'                    => 'COUPONCREATE20',
      'product_name'            => Product::find(2)->name,
      'name'                    => '20% percent off',
      'percentage_off'          => 20,
      'interval'                => Coupon::INTERVAL_MONTH,
      'interval_count'          => 6,
      'condition' => [
        'new_customer_only'     => false,
        'new_subscription_only' => false,
        'upgrade_only'          => false,
        'countries'             => [],
      ],
      'status'                  => 'active',
      'start_date'              => Carbon::tomorrow(),
      'end_date'                => '2099-12-31'
    ];

    $this->modelUpdate = [
      'coupon_event'            => $coupon_event,
      'discount_type'           => Coupon::DISCOUNT_TYPE_PERCENTAGE,
      'type'                    => Coupon::TYPE_SHARED,
      'code'                    => 'COUPONCODE30',
      'product_name'            => Product::find(2)->name,
      'name'                    => '30% percent off',
      'percentage_off'          => 30,
      'interval'                => Coupon::INTERVAL_MONTH,
      'interval_count'          => 6,
      'condition' => [
        'new_customer_only'     => false,
        'new_subscription_only' => false,
        'upgrade_only'          => false,
        'countries'             => [],
      ],
      'status'                  => 'inactive',
      'start_date'              => Carbon::today(),
      'end_date'                => '2099-12-31'
    ];

    $createData = $this->modelCreate;
    $createData['code']         = 'testprecreate20';
    $createData['start_date']   = Carbon::today();
    $createData['status']       = 'active';
    $this->object = Coupon::create($createData);

    $createData = $this->modelCreate;
    $createData['code']         = 'testprecreate30';
    $createData['start_date']   = Carbon::tomorrow();
    $createData['status']       = 'draft';
    $this->object2 = Coupon::create($createData);
  }
}
