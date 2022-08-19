<?php

namespace Tests\Feature;

use Tests\Helper\ApiTestTimeHelper;

class LdsCheckOutApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsCheckInOnlineCheckOutOnlineOk()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckInOfflineCheckOutOnlineOk()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckInOnlineCheckOutOfflineOk()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out (offline)
    $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckInOfflineCheckOutOfflineOk()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest, FALSE);

    // check-out (offline)
    $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckOutExpiredDeviceOK()
  {
    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3600);

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckOutOfflineExpiredDeviceOK()
  {
    // Reverse time by an hour
    ApiTestTimeHelper::setCurrentTime(\time() - 3600);

    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // Reset time to current time
    ApiTestTimeHelper::unsetCurrentTime();

    // check-out (offline)
    $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);
  }

  public function testLdsCheckOutDifferentDevicesCheckInOk()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    // check-in
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

  public function testLdsCheckOutDifferentDevicesCheckInScenario2Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutDifferentDevicesCheckInScenario3Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    // check-in
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

  public function testLdsCheckOutDifferentDevicesCheckInScenario4Ok()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out (offline)
    $response = $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutDifferentDevicesCheckInScenario5Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out (offline)
    $response = $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutDifferentDevicesCheckInScenario6Ok()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    $previousCheckInRequest = $checkInRequest;

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out
    $response = $this->verifyCheckOutResponse($previousCheckInRequest);
    $this->verifyCheckActionDataContent($previousCheckInRequest, $response);
    $this->verifyCheckActionDatabaseContent($previousCheckInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutDifferentDevicesCheckInScenario7Ok()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);

    $previousCheckInRequest = $checkInRequest;

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out
    $response = $this->verifyCheckOutResponse($previousCheckInRequest);
    $this->verifyCheckActionDataContent($previousCheckInRequest, $response);
    $this->verifyCheckActionDatabaseContent($previousCheckInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutDifferentDevicesCheckInScenario8Ok()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out (offline)
    $response = $this->verifyCheckOutResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1, FALSE);

    // check-in (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutWithoutCheckInFail()
  {
    // check-out
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response, 7);

    return $response;
  }

  public function testLdsCheckOutRepeatSameDeviceFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response, 7);
  }

  public function testLdsCheckOutCheckInTooManyDevicesFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    // check-in
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

  public function testLdsCheckOutCheckInTooManyDevicesScenario2Fail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1, FALSE);

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

  public function testLdsCheckOutPreviouslyCheckedOutDeviceFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    $previousCheckInRequest = $checkInRequest;

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1);

    // check-out
    $response = $this->verifyCheckOutResponse($previousCheckInRequest);
    // Assert value 7 - "Device not check-in yet "
    $this->verifyCheckActionDataContent($previousCheckInRequest, $response, 7);
    $this->verifyCheckActionDatabaseContent($previousCheckInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutPreviouslyCheckedOutDeviceScenario2Fail()
  {
    // check-in (offline)
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest, FALSE);

    // check-out
    $response = $this->verifyCheckOutResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 0, FALSE);

    $previousCheckInRequest = $checkInRequest;

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 1);

    // check-out
    $response = $this->verifyCheckOutResponse($previousCheckInRequest);
    // Assert value 7 - "Device not check-in yet "
    $this->verifyCheckActionDataContent($previousCheckInRequest, $response, 7);
    $this->verifyCheckActionDatabaseContent($previousCheckInRequest, 1, FALSE);

    // check-in
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $this->regRequest['device_id'] = $checkInRequest['device_id'];
    $this->regDeviceApi();
    $response = $this->verifyCheckInResponse($checkInRequest);
    $this->verifyCheckActionDataContent($checkInRequest, $response);
    $this->verifyCheckActionDatabaseContent($checkInRequest, 2);
  }

  public function testLdsCheckOutOnlineNotRegisteredDeviceFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    $response = $this->verifyCheckOutResponse($checkInRequest);
    // Verify returns Not Register flag
    $this->verifyCheckActionDataContent($checkInRequest, $response, 6);
  }

  public function testLdsCheckOutOfflineNotRegisteredDeviceFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $response = $this->verifyCheckInResponse($checkInRequest);

    // check-out (offline)
    $checkInRequest['device_id'] = $this->faker->numerify('################');
    // Verify asserts status 400
    $this->verifyCheckOutResponse($checkInRequest, FALSE, 400);
  }

  public function testLdsCheckOutOnlineUnauthorizedUserFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $checkInRequest['user_code'] = $this->createRandomString(14);
    $this->verifyCheckOutResponse($checkInRequest, TRUE, 400);
  }

  public function testLdsCheckOutOnlineUnauthorizedRequestIDFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $checkInRequest['request_id'] = $this->createRandomString(4);
    $this->verifyCheckOutResponse($checkInRequest, TRUE, 400);
  }

  public function testLdsCheckOutOnlineUnauthorizedVersionFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out
    $checkInRequest['version'] = 0;
    $this->verifyCheckOutResponse($checkInRequest, TRUE, 400);
  }

  public function testLdsCheckOutOfflineUnauthorizedUserFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out (offline)
    $checkInRequest['user_code'] = $this->createRandomString(14);
    $this->verifyCheckOutResponse($checkInRequest, FALSE, 400);
  }

  public function testLdsCheckOutOfflineUnauthorizedRequestIDFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out (offline)
    $checkInRequest['request_id'] = $this->createRandomString(4);
    $this->verifyCheckOutResponse($checkInRequest, FALSE, 400);
  }

  public function testLdsCheckOutOfflineUnauthorizedVersionFail()
  {
    // check-in
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();
    $this->verifyCheckInResponse($checkInRequest);

    // check-out (offline)
    $checkInRequest['version'] = 0;
    $this->verifyCheckOutResponse($checkInRequest, FALSE, 400);
  }
}
