<?php

namespace App\Models;

class PaymentMethodMetaPaddle
{
  public function __construct(
    public ?string $payment_method_id,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      payment_method_id: $data['payment_method_id'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'payment_method_id' => $this->payment_method_id,
    ];
  }
}
