<?php

namespace App\Models;

class ProductMeta
{
  public function __construct(public ProductMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: ProductMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
