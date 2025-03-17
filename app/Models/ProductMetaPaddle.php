<?php

namespace App\Models;

class ProductMetaPaddle
{
  public function __construct(
    public ?string $product_1_month_id,
    public ?string $product_1_year_id,
    public ?string $product_2_day_id,
  ) {}

  static public function from(array $data): self
  {
    return new self(
      product_1_month_id: $data['product_1_month_id'] ?? null,
      product_1_year_id: $data['product_1_year_id'] ?? null,
      product_2_day_id: $data['product_2_day_id'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'product_1_month_id'  => $this->product_1_month_id,
      'product_1_year_id'   => $this->product_1_year_id,
      'product_2_day_id'    => $this->product_2_day_id,
    ];
  }

  public function getProductId(ProductInterval $interval): ?string
  {
    $attr = 'product_' . $interval->value . '_id';
    return $this->$attr ?? null;
  }

  public function setProductId(ProductInterval $interval, ?string $productId): self
  {
    $attr = 'product_' . $interval->value . '_id';
    $this->$attr = $productId;
    return $this;
  }
}
