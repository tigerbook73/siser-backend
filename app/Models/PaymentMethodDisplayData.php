<?php

namespace App\Models;

class PaymentMethodDisplayData
{
  public $data;

  public function __construct(
    public ?string $brand,
    public ?int $expiration_year,
    public ?int $expiration_month,
    public ?string $last_four_digits
  ) {}

  static public function from(array $data): self
  {
    return new self(
      brand: $data['brand'] ?? null,
      expiration_year: $data['expiration_year'] ?? null,
      expiration_month: $data['expiration_month'] ?? null,
      last_four_digits: $data['last_four_digits'] ?? null
    );
  }

  public function toArray(): array
  {
    $data = [];
    if ($this->brand) {
      $data['brand'] = $this->brand;
    }
    if ($this->expiration_year) {
      $data['expiration_year'] = $this->expiration_year;
    }
    if ($this->expiration_month) {
      $data['expiration_month'] = $this->expiration_month;
    }
    if ($this->last_four_digits) {
      $data['last_four_digits'] = $this->last_four_digits;
    }
    return $data;
  }
}
