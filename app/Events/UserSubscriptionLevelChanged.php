<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserSubscriptionLevelChanged
{
  use Dispatchable, SerializesModels;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(public User $user)
  {
    //
  }
}
