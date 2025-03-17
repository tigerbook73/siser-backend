<?php

namespace App\Models;

class RefundMeta
{
  public function __construct(public RefundMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: RefundMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
