<?php

namespace App\Models;

use App\Models\Base\LdsInstance as BaseLdsInstance;

class LdsInstance extends BaseLdsInstance
{
  public function beforeCreate()
  {
    $this->status = 'active';
  }
}
