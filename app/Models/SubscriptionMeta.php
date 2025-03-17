<?php

namespace App\Models;

class SubscriptionMeta
{
  public function __construct(public SubscriptionMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: SubscriptionMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
