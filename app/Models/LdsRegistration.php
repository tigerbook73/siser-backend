<?php

namespace App\Models;

use App\Events\LdsRegistered;
use App\Events\LdsUnregistered;
use App\Models\Base\LdsRegistration as BaseLdsRegistration;

class LdsRegistration extends BaseLdsRegistration
{
  protected function beforeCreate()
  {
    $this->status = 'active';
  }

  protected function afterCreate()
  {
    LdsRegistered::dispatch($this);
  }

  protected function afterUpdate()
  {
    if ($this->status == 'active') {
      LdsRegistered::dispatch($this);
    } else {
      LdsUnregistered::dispatch($this);
    }
  }
}
