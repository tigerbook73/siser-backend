<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class PriceCustomData
{
  const DEFAULT_QUANTITY = 1;

  public function __construct(
    public ?string $product_name,
    public ?int $plan_id,
    public ?string $plan_name,
    public ?string $plan_timestamp,
    public ?int $license_package_id,
    public ?int $license_quantity,
    public ?string $license_package_timestamp
  ) {}

  static public function from(?array $data): self
  {
    return new self(
      product_name: $data['product_name'] ?? null,
      plan_id: $data['plan_id'] ?? null,
      plan_name: $data['plan_name'] ?? null,
      plan_timestamp: $data['plan_timestamp'] ?? null,
      license_package_id: $data['license_package_id'] ?? null,
      license_quantity: $data['license_quantity'] ?? self::DEFAULT_QUANTITY,
      license_package_timestamp: $data['license_package_timestamp'] ?? null
    );
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'product_name'       => $this->product_name,
      'plan_id'            => $this->plan_id,
      'plan_name'          => $this->plan_name,
      'plan_timestamp'     => $this->plan_timestamp,
      'license_package_id' => $this->license_package_id,
      'license_quantity'   => $this->license_quantity,
      'license_package_timestamp' => $this->license_package_timestamp,
    ]);
  }
}
