<?php

namespace App\Models;

use App\Events\LdsRegistered;
use App\Models\Base\LdsRegistration as BaseLdsRegistration;

class LdsRegistration extends BaseLdsRegistration
{
  protected function afterCreate()
  {
    LdsRegistered::dispatch($this);
  }

  protected function afterUpdate()
  {
    // not required to send event
    // LdsRegistered::dispatch($this);
  }
}
