<?php

namespace App\Models;

class BillingInfoMetaPaddle
{
  public ?string $customer_id;
  public ?string $address_id;
  public ?string $business_id;

  public function __construct(?array $data = null)
  {
    $this->customer_id  = $data['customer_id'] ?? null;
    $this->address_id   = $data['address_id'] ?? null;
    $this->business_id  = $data['business_id'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'customer_id' => $this->customer_id,
      'address_id'  => $this->address_id,
      'business_id' => $this->business_id,
    ];
  }
}

class BillingInfoMeta
{
  public BillingInfoMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = BillingInfoMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): BillingInfoMeta
  {
    return new BillingInfoMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
