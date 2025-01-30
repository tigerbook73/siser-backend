<?php

namespace App\Models;

class RefundMetaPaddle
{
  public ?string $adjustment_id;
  public ?string $transaction_id;
  public ?string $paddle_timestamp;

  public function __construct(?array $data = null)
  {
    $this->adjustment_id    = $data['adjustment_id'] ?? null;
    $this->transaction_id   = $data['transaction_id'] ?? null;
    $this->paddle_timestamp = $data['paddle_timestamp'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
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

class RefundMeta
{
  public RefundMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = RefundMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): RefundMeta
  {
    return new RefundMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
