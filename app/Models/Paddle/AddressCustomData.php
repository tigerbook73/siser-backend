<?php

namespace App\Models\Paddle;

use Paddle\SDK\Entities\Shared\CustomData;

class AddressCustomData
{
  public ?int $user_id;
  public ?int $billing_info_id;

  static public function from(?array $data): self
  {
    $obj = new self();
    $obj->user_id          = $data['user_id'] ?? null;
    $obj->billing_info_id  = $data['billing_info_id'] ?? null;
    return $obj;
  }

  public function toCustomData(): CustomData
  {
    return new CustomData([
      'user_id'           => $this->user_id,
      'billing_info_id'   => $this->billing_info_id,
    ]);
  }
}
