<?php

namespace App\Services\Lds;

use App\Models\LdsInstance;
use App\Models\LdsLog;
use App\Models\LdsPool;
use App\Models\LdsRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LdsSynchronizer implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


  /**
   * UserSaved, UserDeleted, LdsRegistered
   */
  public string $event;
  public ?User $user = null;
  public ?LdsRegistration $ldsRegistration = null;


  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(string $event, $model)
  {
    $this->event = $event;
    if ($this->event == 'UserSaved' || $this->event == 'UserDeleted') {
      $this->user = $model;
    } else if ($this->event == 'LdsRegistered') {
      $this->ldsRegistration = $model;
    }
    // Log::info("Lds event received {$this->event}.");
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    // TODO: split to different routines
    if ($this->event == 'UserSaved') {
      /** @var LdsPool $pool */
      $pool =  LdsPool::where('user_id', $this->user->id)->first() ?? new LdsPool();
      $pool->fill([
        'user_id'             => $this->user->id,
        'subscription_level'  => $this->user->subscription_level,
        'license_count'       => $this->user->license_count,
        'license_free'        => $this->user->license_count,
      ]);
      $pool->save();
    } else if ($this->event == 'UserDeleted') {
      /** @var LdsPool $pool */
      $pool =  LdsPool::where('user_id', $this->user->id);
      $pool->fill([
        'user_id'             => $this->user->id,
        'subscription_level'  => 0,
        'license_count'       => 0,
        'license_free'        => 0,
      ]);
      $pool->save();
    } else if ($this->event == 'LdsRegistered') {
      /** @var LdsInstance $instance */
      $instance = LdsInstance::where('lds_registration_id', $this->ldsRegistration->id)->first() ?? new LdsInstance();
      $pool_id = $instance->lds_pool_id ?? LdsPool::where('user_id', $this->ldsRegistration->user_id)->first()->id;
      $instance->fill([
        'lds_pool_id'         => $pool_id,
        'lds_registration_id' => $this->ldsRegistration->id,
        'user_id'             => $this->ldsRegistration->user_id,
        'device_id'           => $this->ldsRegistration->device_id,
        'user_code'           => $this->ldsRegistration->user_code,
        'registered_at'       => $this->ldsRegistration->created_at,
        'online'              => false,
        'expires_at'          => 0,
      ]);
      $instance->save();

      LdsLog::log($instance->id, 'reg', 'ok', '');
    }
  }
}
