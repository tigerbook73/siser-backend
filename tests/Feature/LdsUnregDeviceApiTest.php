<?php

namespace Tests\Feature;

use App\Models\LdsLicense;

class LdsUnregDeviceApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsUnregNotOnlineOk()
  {
    $response = $this->regDeviceApi();

    $this->unregDeviceApi($response->json('user_code'));

    $ldsLicense = LdsLicense::fromUserId($this->user->id);
    $this->assertTrue($ldsLicense->license_free == $ldsLicense->license_count);
  }

  public function testLdsUnregOnLineOk()
  {
    $response = $this->regDeviceApi();
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $response->json('user_code');
    $this->verifyCheckInResponse($checkInRequest);

    $ldsLicense = LdsLicense::fromUserId($this->user->id);
    $this->assertTrue($ldsLicense->license_free < $ldsLicense->license_count);

    $this->unregDeviceApi($response->json('user_code'));

    $ldsLicense->refresh();
    $this->assertTrue($ldsLicense->license_free == $ldsLicense->license_count);
  }

  public function testLdsUnregReregOk()
  {
    $response = $this->regDeviceApi();

    $this->unregDeviceApi($response->json('user_code'));

    $this->regDeviceApi();
  }

  public function testLdsReregNotExist()
  {
    $unregRequest = $this->unregRequest;
    $unregRequest['user_code'] = '111222333444555';
    $response = $this->postJson($this->baseUrl . '/unreg-device', $unregRequest);
    $this->assertTrue($response->getStatusCode() >= 400 && $response->getStatusCode() < 500);
  }

  public function testLdsReregRepeatUnReg()
  {
    $response = $this->regDeviceApi();

    $this->unregDeviceApi($response->json('user_code'));

    $unregRequest = $this->unregRequest;
    $unregRequest['user_code'] = $response->json('user_code');
    $response = $this->postJson($this->baseUrl . '/unreg-device', $unregRequest);
    $this->assertTrue($response->getStatusCode() >= 400 && $response->getStatusCode() < 500);
  }

  public function testToDo()
  {
    $this->markTestIncomplete("more test accert && error case to do");
  }
}
