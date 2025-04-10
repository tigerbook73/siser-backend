<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class CouponInfo implements Arrayable
{
  public function __construct(
    public int $id,
    public string $code,
    public string $name,
    public string $product_name,
    public string $type,
    public string $coupon_event,
    public string $discount_type,
    public float $percentage_off,
    public string $interval,
    public int $interval_size,
    public int $interval_count,
    public ?Carbon $starts_at,
    public ?Carbon $ends_at,
    public CouponMeta $meta
  ) {}

  static public function from(array $data): CouponInfo
  {
    return new CouponInfo(
      $data['id'],
      $data['code'],
      $data['name'],
      $data['product_name'],
      $data['type'],
      $data['coupon_event'],
      $data['discount_type'],
      $data['percentage_off'] ?? 0.0,
      $data['interval'],
      $data['interval_size'] ?? 1,
      $data['interval_count'] ?? 1,
      isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : null,
      isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
      CouponMeta::from($data['meta'] ?? []),
    );
  }

  public function toArray(): array
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
      'interval_size'   => $this->interval_size,
      'interval_count'  => $this->interval_count,
      'starts_at'      => $this->starts_at ? $this->starts_at->format('Y-m-d\TH:i:s\Z') : null,
      'ends_at'        => $this->ends_at ? $this->ends_at->format('Y-m-d\TH:i:s\Z') : null,
      'meta'            => $this->meta->toArray(),
    ];
  }
}
