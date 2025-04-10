<?php

namespace App\Models;

class InvoiceMetaPaddle
{
  public function __construct(
    public ?string $subscription_id,
    public ?string $transaction_id,
    public ?string $customer_id,
    public ?string $discount_id,
    public ?string $paddle_timestamp,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      subscription_id: $data['subscription_id'] ?? null,
      transaction_id: $data['transaction_id'] ?? null,
      customer_id: $data['customer_id'] ?? null,
      discount_id: $data['discount_id'] ?? null,
      paddle_timestamp: $data['paddle_timestamp'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'subscription_id'   => $this->subscription_id,
      'transaction_id'    => $this->transaction_id,
      'customer_id'       => $this->customer_id,
      'discount_id'       => $this->discount_id,
      'paddle_timestamp'  => $this->paddle_timestamp,
    ];
  }
}
