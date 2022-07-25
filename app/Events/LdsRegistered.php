<?php

namespace App\Events;

use App\Models\LdsRegistration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LdsRegistered
{
  use Dispatchable, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(public LdsRegistration $ldsRegistration)
  {
    //
  }
}
