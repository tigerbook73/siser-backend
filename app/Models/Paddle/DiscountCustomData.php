<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class DiscountCustomData
{
  public function __construct(
    public ?string $coupon_id,
    public ?string $coupon_event,
    public ?string $coupon_name
  ) {}

  static public function from(?array $data): self
  {
    return new self(
      coupon_id: $data['coupon_id'] ?? null,
      coupon_event: $data['coupon_event'] ?? null,
      coupon_name: $data['coupon_name'] ?? null
    );
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'coupon_id'     => $this->coupon_id,
      'coupon_event'  => $this->coupon_event,
      'coupon_name'   => $this->coupon_name,
    ]);
  }
}
