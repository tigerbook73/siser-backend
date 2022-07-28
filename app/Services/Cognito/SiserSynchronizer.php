<?php

namespace App\Services\Cognito;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SiserSynchronizer implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(public User $user)
  {
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    // TODO: split to different routines
    (new Provider())->updateUserSubscriptionLevel($this->user->name, $this->user->subscription_level);
  }
}
