<?php

namespace App\Models;

use App\Services\Paddle\PaddleSerializer;
use Paddle\SDK\Entities\Transaction\TransactionProration;

class InvoiceItemMeta
{
  public function __construct(
    public string $product_id,
    public string $price_id,
    public ?TransactionProration $proration,
  ) {}

  static function from(array $data): self
  {
    return new self(
      product_id: $data['product_id'] ?? '',
      price_id: $data['price_id'] ?? '',
      proration: isset($data['proration']) ? TransactionProration::from($data['proration']) : null
    );
  }

  public function toArray(): array
  {
    return [
      'product_id' => $this->product_id,
      'price_id' => $this->price_id,
      'proration' => $this->proration ? PaddleSerializer::serialize($this->proration) : null,
    ];
  }
}
