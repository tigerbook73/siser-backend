<?php

namespace App\Models;

class PaymentMethodInfo
{
  public function __construct(
    public string $type,
    public PaymentMethodDisplayData $display_data
  ) {
    $this->type = $type;
    $this->display_data = $display_data;
  }

  static public function from(?array $data): self
  {
    return new self(
      type: $data['type'] ?? null,
      display_data: PaymentMethodDisplayData::from($data['display_data'] ?? [])
    );
  }

  public function toArray(): array
  {
    return [
      'type' => $this->type,
      'display_data' => $this->display_data->toArray()
    ];
  }
}
