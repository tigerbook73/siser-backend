<?php

namespace App\Models;

class ProductMetaPaddle
{
  public ?string $product_id;

  public function __construct(?array $data = null)
  {
    $this->product_id  = $data['product_id'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'product_id' => $this->product_id,
    ];
  }
}

class ProductMeta
{
  public ProductMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = ProductMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): ProductMeta
  {
    return new ProductMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
