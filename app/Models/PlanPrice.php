<?php

namespace App\Models;

class PlanPrice
{
  public function __construct(
    public string $country,
    public string $currency,
    public float $price
  ) {}

  static function from(array $data): self
  {
    return new self(
      country: $data['country'],
      currency: $data['currency'],
      price: (float)$data['price']
    );
  }

  public function toArray(): array
  {
    return [
      'country' => $this->country,
      'currency' => $this->currency,
      'price' => $this->price
    ];
  }
}
