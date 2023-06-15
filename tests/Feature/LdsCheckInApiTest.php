<?php

namespace Tests\Feature;

use App\Models\LdsLicense;
use App\Services\Lds\LdsException;
use Tests\Helper\ApiTestTimeHelper;

class LdsCheckInApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsCheckInOnlineOk()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    return $response;
  }

  public function testLdsCheckInOfflineOK()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    return $response;
  }

  public function testLdsCheckInOnlineMultipleDevicesOk()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckInExpiredDeviceSetOfflineOk()
  {
    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    $checkInRequestPrevious = $checkInRequest;

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1);

    // check-in
    $device = $this->findDevice($checkInRequestPrevious['user_code'], $checkInRequestPrevious['device_id']);
    $this->assertEquals($device->getStatus(), 'offline');
    $this->assertEquals($device->getExpiresAt(),  0);
  }

  public function testLdsCheckInMultipleDevicesKickOutExpiredDeviceOk()
  {
    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckInMultipleOnlineDevicesKickOutExpiredDeviceScenario2Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckInMultipleOnOfflineDevicesKickOutExpiredDeviceScenario3Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckInOnlineSameDeviceTimeExtendedOK()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $first_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    sleep(3);

    // check-in
    $response = $this->verifyCheckInResponse($checkInRequest);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $second_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    $first_time_date = new \DateTime($first_time_expires_at);
    $second_time_date = new \DateTime($second_time_expires_at);
    $since_start = $first_time_date->diff($second_time_date);
    // To assert true that the device's expire date time has been extended
    $this->assertGreaterThanOrEqual(0, $since_start->s);
  }

  public function testLdsCheckInOfflineMultipleDevicesOk()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckInOfflineSameDeviceTimeExtendedOK()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $first_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    sleep(3);

    // check-in (offline)
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $second_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    $first_time_date = new \DateTime($first_time_expires_at);
    $second_time_date = new \DateTime($second_time_expires_at);
    $since_start = $first_time_date->diff($second_time_date);
    // To assert true that the device's expire date time has been extended
    $this->assertGreaterThanOrEqual(0, $since_start->s);
  }

  public function testLdsCheckInOnOfflineSameDeviceTimeExtendedOK()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $first_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    sleep(3);

    // check-in
    $this->verifyCheckInResponse($checkInRequest);
    $device = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($device !== null && $device->getStatus() == 'online');
    $second_time_expires_at = date('Y-m-d H:i:s', $device->getExpiresAt());

    $first_time_date = new \DateTime($first_time_expires_at);
    $second_time_date = new \DateTime($second_time_expires_at);
    $since_start = $first_time_date->diff($second_time_date);
    // To assert true that the device's expire date time has been extended
    $this->assertGreaterThanOrEqual(0, $since_start->s);
  }

  public function testLdsCheckInOnlineTooManyDevicesFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response, LdsException::LDS_ERR_TOO_MANY_DEVICES[0]);
  }

  public function testLdsCheckInOnOfflineTooManyDevicesFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response,  LdsException::LDS_ERR_TOO_MANY_DEVICES[0]);
  }

  public function testLdsCheckInOnOfflineTooManyDevicesScenario2Fail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response, LdsException::LDS_ERR_TOO_MANY_DEVICES[0]);
  }

  public function testLdsCheckInOnOfflineTooManyDevicesScenario3Fail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest);

    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3660);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $this->verifyCheckInResponse($checkInRequest, FALSE, 400);
  }

  public function testLdsCheckInOnlineNotRegisteredDeviceFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    // Verify returns Not Register flag
    $this->verifyCheckActionDataContent($checkInRequest, $response, LdsException::LDS_ERR_DEVICE_NOT_REGISTERED[0]);

    return $response;
  }

  public function testLdsCheckInOfflineNotRegisteredDeviceFail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $checkInRequest['user_code'] = $this->getUserCode();
    // Verify asserts status 400
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE, 400);

    return $response;
  }

  public function testLdsCheckInOnlineUnauthorizedUserFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->createRandomString(14);
    $response = $this->verifyCheckInResponse($checkInRequest, TRUE, 400);

    return $response;
  }

  public function testLdsCheckInOnlineUnauthorizedRequestIDFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['request_id'] = $this->createRandomString(4);
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, TRUE, 400);

    return $response;
  }

  public function testLdsCheckInOnlineUnauthorizedVersionFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['version'] = 0;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, TRUE, 400);

    return $response;
  }

  public function testLdsCheckInOfflineUnauthorizedUserFail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->createRandomString(14);
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE, 400);

    return $response;
  }

  public function testLdsCheckInOfflineUnauthorizedRequestIDFail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['request_id'] = $this->createRandomString(4);
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE, 400);

    return $response;
  }

  public function testLdsCheckInOfflineUnauthorizedVersionFail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['version'] = 0;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE, 400);

    return $response;
  }

  public function testLdsCheckInOnlineUserNoLicenseFail()
  {
    // Manual set user to no licenses
    $ldsLicense = LdsLicense::fromUserId($this->user->id);
    $ldsLicense->license_count = 0;
    $ldsLicense->save();

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    // Verify returns User doesn't have any licenses
    $this->verifyCheckActionDataContent($checkInRequest, $response, LdsException::LDS_ERR_USER_DOESNT_HAVE_LICENSE[0]);

    return $response;
  }
}
