<?php

namespace App\Models;

class BillingAddress
{
  public function __construct(
    public string $line1,
    public string $line2,
    public string $city,
    public string $postcode,
    public string $state,
    public string $country,
  ) {}

  static function from(array $data): self
  {
    return new self(
      line1: $data['line1'] ?? '',
      line2: $data['line2'] ?? '',
      city: $data['city'] ?? '',
      postcode: $data['postcode'] ?? '',
      state: $data['state'] ?? '',
      country: $data['country'],
    );
  }

  public function toArray(): array
  {
    return [
      'line1'     => $this->line1,
      'line2'     => $this->line2,
      'city'      => $this->city,
      'postcode'  => $this->postcode,
      'state'     => $this->state,
      'country'   => $this->country,
    ];
  }
}
