<?php

namespace App\Models;

class PlanMetaPaddle
{
  public function __construct(
    public string $product_id,
    public string $price_id, // single license price id
    public PlanMetaPaddleLicensePrices $license_prices,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      product_id: $data['product_id'] ?? "",
      price_id: $data['price_id'] ?? "",
      license_prices: PlanMetaPaddleLicensePrices::from($data['license_prices'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'product_id' => $this->product_id,
      'price_id' => $this->price_id,
      'license_prices' => $this->license_prices->toArray(),
    ];
  }
}
