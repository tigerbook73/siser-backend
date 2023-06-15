<?php

namespace Tests\Unit;

use App\Models\LdsDevice;
use App\Models\LdsLicense;
use App\Models\User;
use App\Services\Lds\LdsException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LdsLicenseTest extends TestCase
{
  use RefreshDatabase;
  protected $seed = true;

  /** @var User $user */
  public $user = null;

  /** @var LdsLicense $ldsLicense */
  public $ldsLicense = null;

  public $devices = [];

  public function setUp(): void
  {
    parent::setup();

    $this->user = User::first();
    $this->ldsLicense = LdsLicense::fromUserIdAndRefresh($this->user->id);

    for ($i = 0; $i < LdsLicense::MAX_DEVICE_COUNT + 1; $i++) {
      $this->devices[] = [
        'user_code'   => 'test_user_code_' . $i,
        'device_id'   => 'test_device_id_' . $i,
        'device_name' => 'test_device_name_' . $i,
        'client_ip'   => '192.168.1.' . $i,
      ];
    }
  }

  public function assertLastAction(LdsDevice $device, string $action, string $client_ip): void
  {
    $latestAction = $device->getLatestAction();

    $this->assertEquals($action, $latestAction['action']);
    $this->assertEquals($client_ip, $latestAction['client_ip']);
    $this->assertLessThan(2, time() - $device->getLatestAction()['time']);
  }

  public function registerDevice(int $number): void
  {
    $this->ldsLicense->registerDevice($this->devices[$number], $this->devices[$number]['client_ip']);
    $this->ldsLicense->fresh();
  }

  public function unregisterDevice(int $number): void
  {
    $this->ldsLicense->unregisterDevice($this->devices[$number]['device_id'], $this->devices[$number]['client_ip']);
    $this->ldsLicense->fresh();
  }

  public function registerDeviceAssert(int $number): void
  {
    $device = $this->ldsLicense->getDevice($this->devices[$number]['device_id']);
    $this->assertNotNull($device);
    $this->assertEquals($this->devices[$number]['device_id'], $device->getDeviceId());
    $this->assertEquals($this->devices[$number]['user_code'], $device->getUserCode());
    $this->assertEquals($this->devices[$number]['device_name'], $device->getDeviceName());
    $this->assertEquals('offline', $device->getStatus());
    $this->assertEquals(0, $device->getExpiresAt());
    $this->assertLastAction($device, 'register', $this->devices[$number]['client_ip']);
  }

  public function checkInDevice(int $number): void
  {
    $this->ldsLicense->checkInDevice($this->devices[$number]['device_id'], $this->devices[$number]['client_ip']);
    $this->ldsLicense->fresh();
  }

  public function expireDevice(int $number): void
  {
    $devices = $this->ldsLicense->devices;
    $device = LdsDevice::fromArray($devices[$this->devices[$number]['device_id']]);
    $device->setExpiresAt(time() - 1);
    $devices[$device->getDeviceId()] = $device->toArray();
    $this->ldsLicense->devices = $devices;
    $this->ldsLicense->save();
  }

  public function checkOutDevice(int $number): void
  {
    $this->ldsLicense->checkOutDevice(
      $this->devices[$number]['device_id'],
      $this->devices[$number]['client_ip']
    );
    $this->ldsLicense->fresh();
  }

  public function checkInDeviceAssert(int $number): void
  {
    $device = $this->ldsLicense->getDevice($this->devices[$number]['device_id']);
    $this->assertNotNull($device);
    $this->assertEquals('online', $device->getStatus());
    $this->assertLessThan(2, time() + 3600 - $device->getExpiresAt());
    $this->assertLastAction($device, 'checkin', $this->devices[$number]['client_ip']);
  }

  public function checkOutDeviceAssert(int $number): void
  {
    $device = $this->ldsLicense->getDevice($this->devices[$number]['device_id']);
    $this->assertNotNull($device);
    $this->assertEquals('offline', $device->getStatus());
    $this->assertEquals(0, $device->getExpiresAt());
    $this->assertLastAction($device, 'checkout', $this->devices[$number]['client_ip']);
  }

  public function licenseAssert(): void
  {
    $licenseUsed = 0;
    foreach ($this->ldsLicense->devices as $device_id => $deviceData) {
      $device = LdsDevice::fromArray($deviceData);
      $this->assertEquals($device_id, $device->getDeviceId());

      if ($device->getStatus() == 'online') {
        $licenseUsed++;
        $this->assertGreaterThan(time(), $device->getExpiresAt());
      } else {
        $this->assertEquals(0, $device->getExpiresAt());
      }
    }

    $this->assertEquals($licenseUsed, $this->ldsLicense->license_used);
    $this->assertEquals(
      $this->ldsLicense->license_count,
      $this->ldsLicense->license_used + $this->ldsLicense->license_free
    );

    if (count($this->ldsLicense->devices) > 1) {
      $this->assertTrue($this->ldsLicense->latest_expires_at <= $this->ldsLicense->lastest_expires_at);
    } else if (count($this->ldsLicense->devices) == 1) {
      $this->assertEquals($this->ldsLicense->latest_expires_at, $this->ldsLicense->lastest_expires_at);
    } else {
      $this->assertEquals(0, $this->ldsLicense->latest_expires_at);
      $this->assertEquals(0, $this->ldsLicense->lastest_expires_at);
    }
  }

  public function testCreateLdsLicenseFromUserCreation(): void
  {
    $newUser = $this->user->replicate();
    $newUser->cognito_id = "abc";
    $newUser->name = "abc";
    $newUser->email = "abc@abc.com";
    $newUser->save();

    $ldsLicense = LdsLicense::fromUserId($newUser->id);
    $this->assertNotNull($ldsLicense);
    $this->assertEquals($newUser->subscription_level, $ldsLicense->subscription_level);
    $this->assertEquals($newUser->license_count, $ldsLicense->license_count);
  }

  public function testCreateLdsLicenseFromQuery()
  {
    $ldsLicense = LdsLicense::fromUserId($this->user->id);
    $ldsLicense->delete();
    $this->assertNull(LdsLicense::find($ldsLicense->id));

    $ldsLicense = LdsLicense::fromUserId($this->user->id);
    $this->assertNotNull($ldsLicense);
  }

  public function testCreateLdsLicenseUserNotExist()
  {
    $this->expectException(ModelNotFoundException::class);

    LdsLicense::fromUserId(99999);
  }

  public function testRegisterDeviceOk()
  {
    // 0
    $this->registerDevice(0);
    $this->registerDeviceAssert(0);

    // 1
    $this->registerDevice(1);
    $this->registerDeviceAssert(1);

    // 0
    $this->registerDevice(0);
    $this->registerDeviceAssert(0);
  }

  public function testRegisterDeviceTooMany()
  {
    for ($i = 0; $i < ldsLicense::MAX_DEVICE_COUNT; $i++) {
      $this->registerDevice($i);
    }

    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_TOO_MANY_DEVICES));
    $this->registerDevice(ldsLicense::MAX_DEVICE_COUNT);
  }

  public function testUnregisterDeviceOk(): void
  {
    $this->registerDevice(0);
    $this->unregisterDevice(0);

    $this->assertNull($this->ldsLicense->getDevice($this->devices[0]['device_id']));
  }

  public function testUnregisterDeviceNotExist(): void
  {
    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED));
    $this->unregisterDevice(0);
  }

  public function testCheckInOk(): void
  {
    // 0
    $this->registerDevice(0);
    $this->checkInDevice(0);
    $this->checkInDeviceAssert(0);
    $this->licenseAssert();
    $this->assertEquals(1, $this->ldsLicense->license_used);

    // 0 again
    $this->checkInDevice(0);
    $this->checkInDeviceAssert(0);
    $this->licenseAssert();
    $this->assertEquals(1, $this->ldsLicense->license_used);

    // 1
    $this->registerDevice(1);
    $this->checkInDevice(1);
    $this->checkInDeviceAssert(1);
    $this->licenseAssert();
    $this->assertEquals(2, $this->ldsLicense->license_used);

    // 0 again
    $this->checkInDevice(0);
    $this->checkInDeviceAssert(0);
    $this->licenseAssert();
    $this->assertEquals(2, $this->ldsLicense->license_used);
  }

  public function testCheckInNoRegistered(): void
  {
    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED));
    $this->checkInDevice(0);
  }

  public function testCheckInNoLicense(): void
  {
    $this->ldsLicense->license_count = 0;
    $this->ldsLicense->license_free = 0;
    $this->ldsLicense->license_used = 0;

    $this->registerDevice(0);

    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_USER_DOESNT_HAVE_LICENSE));
    $this->checkInDevice(0);
  }

  public function testCheckInTooManyDevice(): void
  {
    // assume user has 2 license

    $this->registerDevice(0);
    $this->registerDevice(1);
    $this->registerDevice(2);

    $this->checkInDevice(0);
    $this->checkInDevice(1);

    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_TOO_MANY_DEVICES));
    $this->checkInDevice(2);
  }

  public function testCheckInExpireOthers(): void
  {
    $this->registerDevice(0);
    $this->registerDevice(1);
    $this->registerDevice(2);

    // 0
    $this->checkInDevice(0);

    // expire 0
    $this->expireDevice(0);
    $this->assertEquals(1, $this->ldsLicense->license_used);

    // 1
    $this->checkInDevice(1);
    $this->checkInDeviceAssert(1);
    $this->licenseAssert();
    $this->assertEquals(1, $this->ldsLicense->license_used);

    // 0
    $this->checkInDevice(0);

    // expire 1
    $this->expireDevice(1);
    $this->assertEquals(2, $this->ldsLicense->license_used);

    // 2
    $this->checkInDevice(2);
    $this->checkInDeviceAssert(2);
    $this->licenseAssert();
    $this->assertEquals(2, $this->ldsLicense->license_used);
  }

  public function testCheckOutOk(): void
  {
    $this->registerDevice(0);
    $this->registerDevice(1);

    // 0 & 1
    $this->checkInDevice(0);
    $this->checkInDevice(1);
    $this->licenseAssert();
    $this->assertEquals(2, $this->ldsLicense->license_used);

    $this->checkOutDevice(0);
    $this->checkOutDeviceAssert(0);
    $this->licenseAssert();
    $this->assertEquals(1, $this->ldsLicense->license_used);

    $this->checkOutDevice(1);
    $this->checkOutDeviceAssert(1);
    $this->licenseAssert();
    $this->assertEquals(0, $this->ldsLicense->license_used);
  }

  public function testCheckOutNotRegister(): void
  {
    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_DEVICE_NOT_REGISTERED));
    $this->checkOutDevice(0);
  }

  public function testCheckOutNotCheckIn(): void
  {
    $this->registerDevice(0);

    $this->expectExceptionObject(new LdsException(LdsException::LDS_ERR_DEVICE_NOT_CHECK_IN));
    $this->checkOutDevice(0);
  }
}
