<?php

namespace App\Models;

class SubscriptionMetaPaddle
{
  public ?string $subscription_id;
  public ?string $customer_id;
  public ?string $product_id;
  public ?string $price_id;
  public ?string $discount_id;
  public ?string $paddle_timestamp;

  public function __construct(?array $data = null)
  {
    $this->subscription_id  = $data['subscription_id'] ?? null;
    $this->customer_id      = $data['customer_id'] ?? null;
    $this->product_id       = $data['product_id'] ?? null;
    $this->price_id         = $data['price_id'] ?? null;
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
      'customer_id'       => $this->customer_id,
      'product_id'        => $this->product_id,
      'price_id'          => $this->price_id,
      'discount_id'       => $this->discount_id,
      'paddle_timestamp'  => $this->paddle_timestamp,
    ];
  }
}

class SubscriptionMeta
{
  public SubscriptionMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = SubscriptionMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): SubscriptionMeta
  {
    return new SubscriptionMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
