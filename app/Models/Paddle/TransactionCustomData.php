<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class TransactionCustomData
{
  public function __construct(
    public ?int $user_id,
    public ?int $plan_id
  ) {}

  static public function from(?array $data): self
  {
    return new self(
      user_id: $data['user_id'] ?? null,
      plan_id: $data['plan_id'] ?? null
    );
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'user_id'           => $this->user_id,
      'plan_id'           => $this->plan_id,
    ]);
  }
}
