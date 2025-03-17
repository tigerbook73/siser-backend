<?php

namespace App\Models;

class BillingInfoMetaPaddle
{
  public function __construct(
    public ?string $customer_id,
    public ?string $address_id,
    public ?string $business_id,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      customer_id: $data['customer_id'] ?? null,
      address_id: $data['address_id'] ?? null,
      business_id: $data['business_id'] ?? null,
    );
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
