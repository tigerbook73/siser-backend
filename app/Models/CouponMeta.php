<?php

namespace App\Models;

class CouponMetaPaddle
{
  public ?string $discount_id;
  public ?string $paddle_timestamp;

  public function __construct(?array $data = null)
  {
    $this->discount_id      = $data['discount_id'] ?? null;
    $this->paddle_timestamp = $data['paddle_timestamp'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'discount_id'       => $this->discount_id,
      'paddle_timestamp'  => $this->paddle_timestamp,
    ];
  }
}

class CouponMeta
{
  public CouponMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = CouponMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): CouponMeta
  {
    return new CouponMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
