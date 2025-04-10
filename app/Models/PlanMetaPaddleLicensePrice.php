<?php

namespace App\Models;

class PlanMetaPaddleLicensePrice
{
  public function __construct(
    public int $quantity,
    public string $price_id,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      quantity: $data['quantity'] ?? 0,
      price_id: $data['price_id'] ?? '',
    );
  }

  public function toArray(): array
  {
    return [
      'quantity' => $this->quantity,
      'price_id' => $this->price_id,
    ];
  }
}
