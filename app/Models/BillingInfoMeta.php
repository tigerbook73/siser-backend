<?php

namespace App\Models;

class BillingInfoMeta
{
  public function __construct(public BillingInfoMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: BillingInfoMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
