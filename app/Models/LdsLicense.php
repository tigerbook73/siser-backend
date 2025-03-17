<?php

namespace App\Models;

use App\Models\Base\LdsLicense as BaseLdsLicense;
use App\Services\Lds\LdsException;
use App\Services\Lds\LdsLog;

class LdsResult
{
  public function __construct(
    public int $subscription_level = 0,
    public int $cutter_number = 0,
  ) {}
}

class LdsLicense extends BaseLdsLicense
{
  public const MAX_DEVICE_COUNT = 100;

  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_count'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_free'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_used'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'latest_expires_at'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'lastest_expires_at'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'devices'             => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  static public function createFromUser(User $user)
  {
    $ldsLicense = new LdsLicense();
    $ldsLicense->user_id            = $user->id;
    $ldsLicense->subscription_level = $user->subscription_level;
    $ldsLicense->license_count      = $user->seat_count;
    $ldsLicense->license_free       = $user->seat_count;
    $ldsLicense->license_used       = 0;
    $ldsLicense->latest_expires_at  = 0;
    $ldsLicense->lastest_expires_at = 0;
    $ldsLicense->devices            = [];
    $ldsLicense->save();
    return $ldsLicense;
  }

  static public function fromUserId(int $user_id): self
  {
    if (!$ldsLicense = LdsLicense::where('user_id', $user_id)->first()) {
      $ldsLicense = self::createFromUser(User::findOrFail($user_id));
    }
    return $ldsLicense;
  }

  static public function fromUserIdAndRefresh(int $user_id): self
  {
    $ldsLicense = self::fromUserId($user_id)->refreshLicense();
    $ldsLicense->save();
    return $ldsLicense;
  }

  public function toResource($userType)
  {
    $license = parent::toResource($userType);

    $devices = [];
    foreach ($license['devices'] as $device) {
      $devices[] = $device;
    }
    $license['devices'] = $devices;
    return $license;
  }

  /**
   * @param array $device_info ['user_code' => '...','device_id' => '...', 'device_name' => '...', ]
   * @throws LdsException
   */
  public function registerDevice(array $device_info, ?string $client_ip = null): LdsResult
  {
    $logContext = [
      'user_id' => $this->user_id,
      'device_id' => $device_info['device_id'],
      'client_ip' => $client_ip,
    ];

    if (count($this->devices) >= LdsLicense::MAX_DEVICE_COUNT && !$this->exists($device_info['device_id'])) {
      LdsLog::info('register', 'nok', 'too many devices', $logContext);
      throw new LdsException(
        LdsException::LDS_ERR_TOO_MANY_DEVICES,
        ['subscription_level' => $this->subscription_level]
      );
    }

    $device = LdsDevice::init($device_info)
      ->register($client_ip);
    $this->setDevice($device)
      ->refreshLicense()
      ->save();
    LdsLog::info('register', 'ok', 'device registered', $logContext);

    return new LdsResult($this->subscription_level);
  }

  public function unregisterDevice(string $device_id, ?string $client_ip = null): LdsResult
  {
    $logContext = [
      'user_id' => $this->user_id,
      'device_id' => $device_id,
      'client_ip' => $client_ip,
    ];

    if (!$device = $this->getDevice($device_id)) {
      LdsLog::info('unregister', 'nok', 'device not registered', $logContext);
      throw new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED);
    }

    $this->removeDevice($device)
      ->refreshLicense()
      ->save();
    LdsLog::info('unregister', 'ok', 'device unregistered', $logContext);

    return new LdsResult($this->subscription_level);
  }

  public function checkInDevice(string $device_id, string $client_ip): LdsResult
  {
    $logContext = [
      'user_id' => $this->user_id,
      'device_id' => $device_id,
      'client_ip' => $client_ip,
    ];

    if (!$device = $this->getDevice($device_id)) {
      LdsLog::info('unregister', 'nok', 'device not registered', $logContext);
      throw new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED);
    }

    // if no license
    if ($this->license_count <= 0) {
      LdsLog::info('check-in', 'nok', "user doesnt have licenses", $logContext);
      throw new LdsException(LdsException::LDS_ERR_USER_DOESNT_HAVE_LICENSE);
    }

    // if already online, extend it for 3600 seconds
    if ($device->getStatus() == 'online') {
      $this->setDevice($device->checkin($client_ip))
        ->refreshLicense()
        ->save();

      LdsLog::info('check-in', 'ok', "device extended", $logContext);
      return new LdsResult($this->subscription_level);
    }

    $this->refreshLicense();
    if ($this->license_free > 0) {;
      $this->setDevice($device->checkin($client_ip))
        ->refreshLicense()
        ->save();

      LdsLog::info('check-in', 'ok', 'device checked-in', $logContext);
      return new LdsResult($this->subscription_level);
    }

    LdsLog::info('check-in', 'nok', "too many devices", $logContext);
    throw new LdsException(
      LdsException::LDS_ERR_TOO_MANY_DEVICES,
      ['subscription_level' => $this->subscription_level]
    );
  }

  public function checkOutDevice(string $device_id, $client_ip = null): LdsResult
  {
    $logContext = [
      'user_id' => $this->user_id,
      'device_id' => $device_id,
      'client_ip' => $client_ip,
    ];

    if (!$device = $this->getDevice($device_id)) {
      LdsLog::info('check-in', 'ok', "device not registered", $logContext);
      throw new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED);
    };

    // not online
    if ($device->getStatus() != 'online') {
      LdsLog::info('check-out', 'nok', "device not checked-in", $logContext);
      throw new LdsException(LdsException::LDS_ERR_DEVICE_NOT_CHECK_IN);
    }

    $this->setDevice($device->checkout($client_ip))
      ->refreshLicense()
      ->save();
    LdsLog::info('check-out', 'ok', "device checked-out", $logContext);
    return new LdsResult($this->subscription_level);
  }

  public function updateSubscriptionLevel(int $subscription_level, int $license_count)
  {
    $this->subscription_level = $subscription_level;
    $this->license_count = $license_count;
    $this->refreshLicense();
    $this->save();

    LdsLog::info('update-level', 'ok', 'updated subscription', ['user_id' => $this->user_id]);
  }

  protected function refreshLicense(): self
  {
    $license_used = 0;
    $latest_expires_at = 0;
    $lastest_expires_at = 0;

    // update license_used & license_free
    $now = time();
    foreach ($this->devices as $device_id => $deviceData) {
      $device = LdsDevice::fromArray($deviceData);

      if ($device->getStatus() !== 'online') {
        continue;
      }

      if ($this->license_count <= 0) {
        $this->setDevice($device->checkout());

        LdsLog::info('revoke', 'ok', 'license revoked', [
          'user_id' => $this->user_id,
          'device_id' => $device->getDeviceId(),
        ]);
      } else if ($device->getExpiresAt() < $now) {
        $this->setDevice($device->checkout());

        LdsLog::info('exipre', 'ok', 'device expired', [
          'user_id' => $this->user_id,
          'device_id' => $device->getDeviceId(),
        ]);
      } else {
        $license_used++;
        if ($latest_expires_at == 0 || $device->getExpiresAt() < $latest_expires_at) {
          $latest_expires_at = $device->getExpiresAt();
        }
        if ($lastest_expires_at == 0 || $device->getExpiresAt() > $lastest_expires_at) {
          $lastest_expires_at = $device->getExpiresAt();
        }
      }
    }

    $this->license_used = $license_used;
    $this->license_free = ($this->license_count > $license_used) ? $this->license_count - $license_used : 0;
    $this->latest_expires_at = $latest_expires_at;
    $this->lastest_expires_at = $lastest_expires_at;

    return $this;
  }

  public function exists(string $device_id): bool
  {
    return isset($this->devices[$device_id]);
  }

  public function getDevice(string $device_id): ?LdsDevice
  {
    $devices = $this->devices ?? [];
    if (isset($devices[$device_id])) {
      return LdsDevice::fromArray($devices[$device_id]);
    }
    return null;
  }

  protected function setDevice(LdsDevice $device): self
  {
    $devices = $this->devices ?? [];
    $devices[$device->getDeviceId()] = $device->toArray();
    $this->devices = $devices;
    return $this;
  }

  public function removeDevice(LdsDevice $device): self
  {
    $devices = $this->devices ?? [];
    if (isset($devices[$device->getDeviceId()])) {
      unset($devices[$device->getDeviceId()]);
      $this->devices = $devices;
    }
    return $this;
  }
}
