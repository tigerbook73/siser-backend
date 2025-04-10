<?php

namespace App\Models;

class RefundMetaPaddle
{
  public function __construct(
    public ?string $adjustment_id,
    public ?string $transaction_id,
    public ?string $paddle_timestamp,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      adjustment_id: $data['adjustment_id'] ?? null,
      transaction_id: $data['transaction_id'] ?? null,
      paddle_timestamp: $data['paddle_timestamp'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'adjustment_id'     => $this->adjustment_id,
      'transaction_id'    => $this->transaction_id,
      'paddle_timestamp'  => $this->paddle_timestamp,
    ];
  }
}
