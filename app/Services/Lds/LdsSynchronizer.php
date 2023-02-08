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
use Illuminate\Support\Facades\DB;
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
    } else if ($this->event == 'LdsRegistered' || $this->event == 'LdsUnregistered') {
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
      $this->handleUserSaved();
    } else if ($this->event == 'UserDeleted') {
      $this->handleUserDeleted();
    } else if ($this->event == 'LdsRegistered') {
      $this->handleLdsRegistered();
    } else if ($this->event == 'LdsUnregistered') {
      $this->handleLdsUnregistered();
    }
  }

  public function handleUserSaved()
  {
    /** @var LdsPool $pool */
    $pool =  LdsPool::where('user_id', $this->user->id)->first() ?? new LdsPool();
    $pool->fill([
      'user_id'             => $this->user->id,
      'subscription_level'  => $this->user->subscription_level,
      'license_count'       => $this->user->license_count,
      'license_free'        => $this->user->license_count,
    ]);
    $pool->save();
  }

  public function handleUserDeleted()
  {
    /** @var LdsPool $pool */
    $pool =  LdsPool::where('user_id', $this->user->id);
    $pool->fill([
      'user_id'             => $this->user->id,
      'subscription_level'  => 0,
      'license_count'       => 0,
      'license_free'        => 0,
    ]);
    $pool->save();
  }

  public function handleLdsRegistered()
  {
    /** @var LdsInstance $instance */
    $instance = LdsInstance::where('lds_registration_id', $this->ldsRegistration->id)->first() ?? new LdsInstance();
    if (!$instance->online) {
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
        'status'              => 'active',
      ]);
      $instance->save();
    }

    LdsLog::log($instance->id, 'reg', 'ok', "user:$instance->user_id device:$instance->device_id");
  }

  public function handleLdsUnregistered()
  {
    DB::transaction(function () {
      /** @var LdsInstance|null $instance */
      $instance = LdsInstance::where('lds_registration_id', $this->ldsRegistration->id)->first();
      if ($instance) {
        // release license
        if ($instance->online) {
          (new LdsLicenseManager)->release($instance->user_code, $instance->device_id);
          $instance->refresh();
        }

        // deactivate instance
        $instance->status = 'inactive';
        $instance->save();

        LdsLog::log($instance->id, 'unreg', 'ok', "user:$instance->user_id device:$instance->device_id");
      }
    });
  }
}
