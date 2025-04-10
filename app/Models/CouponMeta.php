<?php

namespace App\Models;

class CouponMeta
{
  public function __construct(public CouponMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: CouponMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
