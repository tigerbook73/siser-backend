<?php

namespace Tests\Feature;

use App\Models\LdsInstance;
use App\Models\LdsPool;

class LdsUnregDeviceApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsUnregNotOnlineOk()
  {
    $response = $this->regDeviceApi();

    $this->unregDeviceApi($response->json('user_code'));

    /** @var LdsPool $ldsPool */
    $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
    $this->assertTrue($ldsPool->license_free == $ldsPool->license_count);
  }

  public function testLdsUnregOnLineOk()
  {
    $response = $this->regDeviceApi();
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $response->json('user_code');
    $this->verifyCheckInResponse($checkInRequest);

    /** @var LdsPool $ldsPool */
    $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
    $this->assertTrue($ldsPool->license_free < $ldsPool->license_count);

    $this->unregDeviceApi($response->json('user_code'));

    $ldsPool->refresh();
    $this->assertTrue($ldsPool->license_free == $ldsPool->license_count);
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
    $response->assertStatus(404);
  }

  public function testLdsReregRepeatUnReg()
  {
    $response = $this->regDeviceApi();

    $this->unregDeviceApi($response->json('user_code'));

    $unregRequest = $this->unregRequest;
    $unregRequest['user_code'] = $response->json('user_code');
    $response = $this->postJson($this->baseUrl . '/unreg-device', $unregRequest);
    $response->assertStatus(404);
  }

  public function testToDo()
  {
    $this->markTestIncomplete("more test accert && error case to do");
  }
}
