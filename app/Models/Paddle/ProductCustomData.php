<?php

namespace App\Models\Paddle;

use App\Models\ProductInterval;
use Paddle\SDK\Entities\Shared\CustomData;

class ProductCustomData
{
  public function __construct(
    public ?int $product_id,
    public ?string $product_name,
    public ?string $product_type,
    public ?ProductInterval $product_interval,
    public ?string $product_timestamp
  ) {}

  static public function from(?array $data): self
  {
    return new self(
      product_id: $data['product_id'] ?? null,
      product_name: $data['product_name'] ?? null,
      product_type: $data['product_type'] ?? null,
      product_interval: ProductInterval::tryFrom($data['product_interval'] ?? ""),
      product_timestamp: $data['product_timestamp'] ?? null
    );
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'product_id'          => $this->product_id,
      'product_name'        => $this->product_name,
      'product_type'        => $this->product_type,
      'product_interval'    => $this->product_interval?->value,
      'product_timestamp'   => $this->product_timestamp,
    ]);
  }
}
