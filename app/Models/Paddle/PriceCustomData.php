<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class PriceCustomData
{
  public ?string $product_name;
  public ?int $plan_id;
  public ?string $plan_name;
  public ?string $plan_timestamp;

  public ?int $license_plan_id;
  public ?string $license_plan_name;
  public ?string $license_plan_timestamp;
  public ?string $quantity;



  static public function from(?array $data): self
  {
    $obj = new self();
    $obj->product_name    = $data['product_name'] ?? null;
    $obj->plan_id         = $data['plan_id'] ?? null;
    $obj->plan_name       = $data['plan_name'] ?? null;
    $obj->plan_timestamp  = $data['plan_timestamp'] ?? null;

    $obj->license_plan_id         = $data['license_plan_id'] ?? null;
    $obj->license_plan_name       = $data['license_plan_name'] ?? null;
    $obj->license_plan_timestamp  = $data['license_plan_timestamp'] ?? null;
    $obj->quantity                = $data['quantity'] ?? null;

    return $obj;
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'product_name'    => $this->product_name,
      'plan_id'         => $this->plan_id,
      'plan_name'       => $this->plan_name,
      'plan_timestamp'  => $this->plan_timestamp,

      'license_plan_id'         => $this->license_plan_id,
      'license_plan_name'       => $this->license_plan_name,
      'license_plan_timestamp'  => $this->license_plan_timestamp,
      'quantity'                => $this->quantity,
    ]);
  }
}
