<?php

namespace App\Models;

class InvoiceMeta
{
  public function __construct(public InvoiceMetaPaddle $paddle) {}

  static public function from(array $data): self
  {
    return new self(
      paddle: InvoiceMetaPaddle::from($data['paddle'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
