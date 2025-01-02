<?php

namespace App\Models;

use App\Models\Base\Coupon as BaseCoupon;

class Coupon extends BaseCoupon
{
  const TYPE_SHARED               = 'shared';
  const TYPE_ONCE_OFF             = 'once-off';

  const INTERVAL_DAY              = 'day';
  const INTERVAL_WEEK             = 'week';
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

  public function info()
  {
    return [
      'id'              => $this->id,
      'code'            => $this->code,
      'name'            => $this->name,
      'product_name'    => $this->product_name,
      'type'            => $this->type,
      'coupon_event'    => $this->coupon_event,
      'discount_type'   => $this->discount_type,
      'percentage_off'  => $this->percentage_off,
      'interval'        => $this->interval,
      'interval_count'  => $this->interval_count,
    ];
  }

  /**
   * set the usage of the coupon, including:
   *  - subscription_id:  the latest subscription_id will be stored
   *  - count:            the number of usage
   * @param int $subscription_id
   */
  public function setUsage(int $subscription_id): self
  {
    if ($this->type === self::TYPE_ONCE_OFF) {
      $this->status = self::STATUS_INACTIVE;
    }

    $usage = $this->usage ?? [];
    $usage['subscription_id'] = $subscription_id;
    $usage['count'] = ($usage['count'] ?? 0) + 1;
    $usage['updated_at'] = now()->toString();
    $this->usage = $usage;
    return $this;
  }

  public function releaseUsage(int $subscription_id): self
  {
    if (!$usage = $this->usage) {
      return $this;
    }

    if ($this->type === self::TYPE_ONCE_OFF && ($usage['subscription_id'] ?? null) === $subscription_id) {
      $this->status = self::STATUS_ACTIVE;
    }

    $usage['subscription_id'] = null;
    $usage['count'] = ($usage['count'] ?? 1) - 1;
    $usage['updated_at'] = now()->toString();
    $this->usage = $usage;
    return $this;
  }

  public function getMeta(): CouponMeta
  {
    return CouponMeta::from($this->meta);
  }

  public function setMeta(CouponMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleDiscountId(?string $paddleDiscountId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->discount_id = $paddleDiscountId;
    return $this->setMeta($meta);
  }

  public function setMetaPaddleTimestamp(?string $paddleTimestamp): self
  {
    $meta = $this->getMeta();
    $meta->paddle->paddle_timestamp = $paddleTimestamp;
    return $this->setMeta($meta);
  }
}
