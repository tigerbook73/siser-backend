<?php

namespace App\Models;

use App\Models\Base\Coupon as BaseCoupon;

class Coupon extends BaseCoupon
{
  const TYPE_SHARED               = 'shared';
  const TYPE_ONCE_OFF             = 'once-off';

  const INTERVAL_DAY              = 'day';
  const INTERVAL_MONTH            = 'month';
  const INTERVAL_YEAR             = 'year';
  const INTERVAL_LONGTERM         = 'longterm';

  const DISCOUNT_TYPE_FREE_TRIAL  = 'free-trial';
  const DISCOUNT_TYPE_PERCENTAGE  = 'percentage';

  const STATUS_DRAFT              = 'draft';
  const STATUS_ACTIVE             = 'active';
  const STATUS_INACTIVE           = 'inactive';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'code'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'product_name'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'type'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'coupon_event'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'discount_type'       => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'percentage_off'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'interval'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'interval_size'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'interval_count'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'start_date'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'end_date'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'usage'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'meta'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  protected function beforeSave()
  {
    $this->code = strtoupper($this->code);
    $this->condition = []; // for compatibility

    // update interval_count to 0 if interval is longterm
    if ($this->interval == self::INTERVAL_LONGTERM) {
      $this->interval_count = 0;
    }

    // update interveral to longterm if interval_count is 0
    if ($this->interval_count == 0) {
      $this->interval = self::INTERVAL_LONGTERM;
    }

    CouponEvent::upsert(
      [['name' => $this->coupon_event]],
      ['name']
    );
  }

  public function scopePublic($query)
  {
    return $query->where('status', Coupon::STATUS_ACTIVE);
  }

  public function getProductInterval(): ProductInterval
  {
    return ProductInterval::build($this->interval, $this->interval_size);
  }

  public function info(): CouponInfo
  {
    return new CouponInfo(
      id: $this->id,
      code: $this->code,
      name: $this->name,
      product_name: $this->product_name,
      type: $this->type,
      coupon_event: $this->coupon_event,
      discount_type: $this->discount_type,
      percentage_off: $this->percentage_off,
      interval: $this->interval,
      interval_size: $this->interval_size,
      interval_count: $this->interval_count
    );
  }

  public function getMeta(): CouponMeta
  {
    return CouponMeta::from($this->meta ?? []);
  }

  public function setMeta(CouponMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleDiscountId(?string $paddleDiscountId): self
  {
    $meta = $this->getMeta();
    if ($meta->paddle->discount_id != $paddleDiscountId) {
      $meta->paddle->discount_id = $paddleDiscountId;
      $this->setMeta($meta);
    }
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTimestamp(?string $paddleTimestamp): self
  {
    $meta = $this->getMeta();
    if ($meta->paddle->paddle_timestamp != $paddleTimestamp) {
      $meta->paddle->paddle_timestamp = $paddleTimestamp;
      $this->setMeta($meta);
    }
    return $this->setMeta($meta);
  }
}
