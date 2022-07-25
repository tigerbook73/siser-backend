<?php

namespace App\Models;

use App\Events\LdsRegistered;
use App\Models\Base\LdsRegistration as BaseLdsRegistration;

class LdsRegistration extends BaseLdsRegistration
{
  /**
   * The event map for the model.
   *
   * @var array
   */
  protected $dispatchesEvents = [
    'saved' => LdsRegistered::class,
  ];
}
