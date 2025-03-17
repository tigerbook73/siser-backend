<?php

namespace App\Models;

class PaymentMethodMeta
{
  public function __construct(public PaymentMethodMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: PaymentMethodMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
