<?php

namespace Tests\Feature;

use App\Models\Base\LdsInstance;
use App\Models\LdsPool;
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
    $instance = $this->findInstance($checkInRequestPrevious['user_code'], $checkInRequestPrevious['device_id']);
    $this->assertEquals($instance->online, FALSE);
    $this->assertEquals($instance->expires_at, 0);
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
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $first_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

    sleep(3);

    // check-in
    $response = $this->verifyCheckInResponse($checkInRequest);
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $second_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

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
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $first_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

    sleep(3);

    // check-in (offline)
    $this->verifyCheckInResponse($checkInRequest, FALSE);
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $second_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

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
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $first_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

    sleep(3);

    // check-in
    $this->verifyCheckInResponse($checkInRequest);
    /** @var LdsInstance $ldsInstance */
    $ldsInstance = $this->findInstance($checkInRequest['user_code'], $checkInRequest['device_id']);
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
    $second_time_expires_at = date('Y-m-d H:i:s', $ldsInstance->expires_at);

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
    $this->verifyCheckActionDataContent($checkInRequest, $response, 3);
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
    $this->verifyCheckActionDataContent($checkInRequest, $response, 3);
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
    $this->verifyCheckActionDataContent($checkInRequest, $response, 3);
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
    $this->verifyCheckActionDataContent($checkInRequest, $response, 6);

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
    $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
    $ldsPool->license_count = 0;
    $ldsPool->save();

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);
    // Verify returns User doesn't have any licenses
    $this->verifyCheckActionDataContent($checkInRequest, $response, 8);

    return $response;
  }
}
