<?php

namespace App\Models;

class InvoiceMetaPaddle
{
  public ?string $subscription_id;
  public ?string $transaction_id;
  public ?string $customer_id;
  public ?string $discount_id;
  public ?string $paddle_timestamp;

  public function __construct(?array $data = null)
  {
    $this->subscription_id  = $data['subscription_id'] ?? null;
    $this->transaction_id   = $data['transaction_id'] ?? null;
    $this->customer_id      = $data['customer_id'] ?? null;
    $this->discount_id      = $data['discount_id'] ?? null;
    $this->paddle_timestamp = $data['paddle_timestamp'] ?? null;
  }

  static public function from(?array $data): self
  {
    return new self($data);
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

class InvoiceMeta
{
  public InvoiceMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = InvoiceMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): InvoiceMeta
  {
    return new InvoiceMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
