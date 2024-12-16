<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class ProductCustomData
{
  public ?int $product_id;
  public ?string $product_name;
  public ?string $product_type;
  public ?string $product_timestamp;


  static public function from(?array $data): self
  {
    $obj = new self();
    $obj->product_id        = $data['product_id'] ?? null;
    $obj->product_name      = $data['product_name'] ?? null;
    $obj->product_type      = $data['product_type'] ?? null;
    $obj->product_timestamp = $data['product_timestamp'] ?? null;
    return $obj;
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'product_id'          => $this->product_id,
      'product_name'        => $this->product_name,
      'product_type'        => $this->product_type,
      'product_timestamp'   => $this->product_timestamp,
    ]);
  }
}
