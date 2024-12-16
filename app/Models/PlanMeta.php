<?php

namespace App\Models;

class PlanMetaPaddle
{
  public ?string $product_id;
  public ?string $price_id;

  public function __construct(?array $data = null)
  {
    $this->product_id  = $data['product_id'] ?? null;
    $this->price_id   = $data['price_id'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'product_id' => $this->product_id,
      'price_id'  => $this->price_id,
    ];
  }
}

class PlanMeta
{
  public PlanMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = PlanMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): PlanMeta
  {
    return new PlanMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
