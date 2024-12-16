<?php

namespace App\Models;

class PaymentMethodMetaPaddle
{
  public ?string $payment_method_id;

  public function __construct(?array $data = null)
  {
    $this->payment_method_id = $data['payment_method_id'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'payment_method_id' => $this->payment_method_id,
    ];
  }
}

class PaymentMethodMeta
{
  public PaymentMethodMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = PaymentMethodMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): PaymentMethodMeta
  {
    return new PaymentMethodMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
