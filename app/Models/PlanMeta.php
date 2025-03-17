<?php

namespace App\Models;

class PlanMeta
{
  public function __construct(public PlanMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: PlanMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
