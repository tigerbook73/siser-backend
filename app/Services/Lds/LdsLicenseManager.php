<?php

namespace App\Services\Lds;

use App\Models\Base\LdsInstance;
use App\Models\LdsLog;
use App\Models\LdsPool;
use Illuminate\Support\Facades\DB;


class ApplyResult
{
  public function __construct(
    public int $subscription_level = 0,
    public int $cutter_number = 0,
  ) {
  }
}

class LdsLicenseManager
{
  public function findPool(int $user_id): ?LdsPool
  {
    return LdsPool::where('user_id', $user_id)->first();
  }

  public function findInstance(string $user_code, string $device_id): ?LdsInstance
  {
    return LdsInstance::where('user_code', $user_code)->where('device_id', $device_id)->first();
  }

  /**
   * @throws LdsException if any error occur
   */
  public function apply(string $user_code, $device_id): ApplyResult
  {
    return DB::transaction(function () use ($user_code, $device_id) {
      if (!$instance = $this->findInstance($user_code, $device_id)) {
        throw new LdsException(LDS_ERR_DEVICE_NOT_REGISTERED);
      };
      $pool = $instance->lds_pool;

      // if no license
      if ($pool->license_count <= 0) {
        LdsLog::log($instance->id, 'check-in', 'nok', 'user doesnt have licenses');
        throw new LdsException(LDS_ERR_USER_DOESNT_HAVE_LICENSE);
      }

      // if already online, extend it for 3600 seconds
      if ($instance->online) {
        $instance->expires_at = time() + 3600;
        $instance->save();

        LdsLog::log($instance->id, 'check-in', 'ok', 'extended');
        return new ApplyResult($pool->subscription_level);
      }

      // step 1: revoke expired license belong to this pool
      /** @var LdsInstance[] $expiredInstances */
      $expiredInstances = $pool->lds_instances()
        ->where('online', true)
        ->where('expires_at', '<', time())
        ->get();
      if (count($expiredInstances) > 0) {
        foreach ($expiredInstances as $expiredInstance) {
          $expiredInstance->online = false;
          $expiredInstance->expires_at = 0;
          $expiredInstance->save();
        }
        $pool->license_free += count($expiredInstances);
        if ($pool->license_free > $pool->license_count) {
          $pool->license_free = $pool->license_count;
        }
      }

      // step 2: check license availability & allocate license
      if ($pool->license_free > 0) {
        $pool->license_free--;
        $pool->save();

        $instance->online = true;
        $instance->expires_at = time() + 3600;
        $instance->save();

        LdsLog::log($instance->id, 'check-in', 'ok', 'check-in');
        return new ApplyResult($pool->subscription_level);
      }

      LdsLog::log($instance->id, 'check-in', 'nok', 'no free license');
      throw new LdsException(LDS_ERR_TOO_MANY_DEVICES);
    });
  }

  /**
   * @throws LdsException if any error occur
   */
  public function release(string $user_code, string $device_id)
  {
    DB::transaction(function () use ($user_code, $device_id) {
      if (!$instance = $this->findInstance($user_code, $device_id)) {
        throw new LdsException(LDS_ERR_DEVICE_NOT_REGISTERED);
      };
      $pool = $instance->lds_pool;

      // not online
      if (!$instance->online) {
        LdsLog::log($instance->id, 'check-out', 'nok', 'instance not online');
        throw new LdsException(LDS_ERR_DEVICE_NOT_CHECK_IN);
      }

      $instance->online = false;
      $instance->expires_at = 0;
      $instance->save();

      if ($pool->license_free < $pool->license_count) {
        $pool->license_free++;
        $pool->save();
      }

      LdsLog::log($instance->id, 'check-out', 'ok', 'check-out');
    });
  }

  /**
   * Loop the LdsIntance and revoke timeout license
   * This function shall be called periodically
   * 
   * @param int $count maximal number of records to process
   * @return number number of record processed, 0 means there is not more records to process
   */
  public function timeout(int $count = 100)
  {
    $instances = LdsInstance::where('online', true)
      ->where('expires_at', '<', time())
      ->limit($count)
      ->get();
    foreach ($instances as $instance) {
      $instance->online = false;
      $instance->expires_at = 0;
      $instance->save();

      LdsLog::Log($instance->id, 'check-out', 'ok', 'expired check-out');
    };

    return count($instances);
  }
}
