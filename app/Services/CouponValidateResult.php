<?php

namespace App\Services;

use App\Models\CouponInfo;

class CouponValidateResult
{
  public function __construct(
    public bool $applicable,
    public CouponValidateResultCode $result_code,
    public string $result_text,
    public ?CouponInfo $coupon_info = null
  ) {}

  public function toArray(): array
  {
    return [
      'applicable' => $this->applicable,
      'result_code' => $this->result_code,
      'result_text' => $this->result_text,
      'coupon' => $this->coupon_info?->toArray(),
    ];
  }
}
