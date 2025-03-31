<?php

namespace App\Models;

class PlanInfo
{
  public function __construct(
    public int $id,
    public string $name,
    public string $product_name,
    public string $description,
    public string $interval,
    public int $interval_count,
    public PlanPrice $price,
    public int $subscription_level,
    public string $url,
  ) {}

  static function from(array $data): self
  {
    return new self(
      id: $data['id'] ?? 0,
      name: $data['name'] ?? '',
      product_name: $data['product_name'] ?? '',
      description: $data['description'] ?? '',
      interval: $data['interval'] ?? '',
      interval_count: $data['interval_count'] ?? 1,
      price: isset($data['price']) ? PlanPrice::from($data['price']) : null,
      subscription_level: $data['subscription_level'] ?? 1,
      url: $data['url'] ?? ''
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'product_name' => $this->product_name,
      'description' => $this->description,
      'interval' => $this->interval,
      'interval_count' => $this->interval_count,
      'price' => $this->price->toArray(),
      'subscription_level' => $this->subscription_level,
      'url' => $this->url,
    ];
  }
}
