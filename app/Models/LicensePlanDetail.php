<?php

namespace App\Models;

/**
 * Detail for the certain quantity of a license plan
 */
class LicensePlanDetail
{
  public string   $name;
  public int      $quantity;
  public float    $price_rate;
  public ?string  $paddle_price_id;

  public function __construct(?array $data = null)
  {
    $this->name             = $data['name'] ?? '';
    $this->quantity         = $data['quantity'] ?? 0;
    $this->price_rate       = $data['price_rate'] ?? 0;
    $this->paddle_price_id  = $data['paddle_price_id'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'name'            => $this->name,
      'quantity'        => $this->quantity,
      'price_rate'      => $this->price_rate,
      'paddle_price_id' => $this->paddle_price_id,
    ];
  }
}
