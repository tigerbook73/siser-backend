<?php

namespace App\Models;

class CouponMetaPaddle
{
  public function __construct(
    public ?string $discount_id,
    public ?string $paddle_timestamp,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      discount_id: $data['discount_id'] ?? null,
      paddle_timestamp: $data['paddle_timestamp'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'discount_id'       => $this->discount_id,
      'paddle_timestamp'  => $this->paddle_timestamp,
    ];
  }
}
