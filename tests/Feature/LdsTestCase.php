<?php

namespace App\Models {
  function time()
  {
    return \Tests\Feature\LdsTestCase::fakeTime();
  }
}

namespace Tests\Feature {

  use App\Models\LdsDevice;
  use App\Services\Lds\LdsCoding;
  use Tests\ApiTestCase;
  use App\Models\LdsLicense;
  use App\Services\Lds\LdsException;
  use Tests\Helper\ApiTestTimeHelper;

  class LdsTestCase extends ApiTestCase
  {
    public string $baseUrl = '/api/v1/lds';
    public string $model = LdsLicense::class;

    public $regRequest = [
      'version' => 1,
      'device_id' => '0000111100002222',
      'device_name' => 'test-computer',
    ];

    public $regResponseSchema = [
      'version',
      'device_id',
      'device_name',
      'user_code',
    ];

    public $checkInRequest = [
      'version'     => 1,
      'request_id'  => '10101',
      'device_id'   => '0000111100002222',
      'user_code'   => '',
    ];

    public $unregRequest = [
      'version' => 1,
      'user_code' => '',
      'device_id' => '0000111100002222',
    ];

    protected function encodeRequest(array $request)
    {
      return (new LdsCoding)->encodeJsonText(json_encode($request));
    }

    protected function decodeResponse(string $result)
    {
      // remove new line & space
      return (new LdsCoding)->decodeJsonText($result);
    }

    static public function fakeTime()
    {
      return ApiTestTimeHelper::getTime();
    }

    protected function getUserCode()
    {
      $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
      return $response->json('user_code');
    }

    protected function verifyCheckInResponse(array $checkInRequest, bool $isOnline = TRUE, int $status = 200)
    {
      $paramString = http_build_query([
        'rq' => $this->encodeRequest($checkInRequest),
        'online' => $isOnline ? 1 : 0,
      ]);

      // check-in
      $response = $this->get("/check-in?$paramString");
      $response->assertStatus($status);

      return $response;
    }

    protected function verifyCheckOutResponse(array $checkInRequest, bool $isOnline = TRUE, int $status = 200)
    {
      $paramString = http_build_query([
        'rq' => $this->encodeRequest($checkInRequest),
        'online' => $isOnline ? 1 : 0,
      ]);

      // check-in
      $response = $this->get("/check-out?$paramString");
      $response->assertStatus($status);

      return $response;
    }

    protected function verifyCheckActionDataContent(array $checkInRequest, object $response, int $errorCode = 0)
    {
      $content = $response->getContent();
      $this->assertTrue(strpos($content, 'BeginLDSData') !== FALSE);

      $content = $response->getContent();
      $this->assertTrue(strpos($content, '~EndLDSData ') !== FALSE);

      $actualContent = str_replace("\r\n", '', str_replace('DSData -->', '', str_replace('~EndL', '', str_replace('BeginLDSData', '', str_replace('<!-- ', '', $content)))));
      $decodedContent = $this->decodeResponse($actualContent);
      $object = json_decode($decodedContent, FALSE);
      $this->assertEquals($object->request_id, $checkInRequest['request_id']);
      $this->assertEquals($object->version, $checkInRequest['version']);
      $this->assertEquals($object->cutter_number, 0);
      $this->assertEquals($object->error_code, $errorCode);
      $this->assertEquals($object->result_code, 0);

      if ($object->error_code == LdsException::LDS_ERR_TOO_MANY_DEVICES[0]) {
        $this->assertTrue($object->subscription_level > 0);
      }
    }

    protected function verifyCheckActionDatabaseContent(array $checkInRequest, int $count = 1, bool $isOnline = TRUE)
    {
      $ldsDevice = $this->findDevice($checkInRequest['user_code'], $checkInRequest['device_id']);
      if ($isOnline) {
        $this->assertTrue($ldsDevice !== null && $ldsDevice->getStatus() == 'online');
      } else {
        $this->assertTrue($ldsDevice !== null && $ldsDevice->getStatus() != 'online');
      }

      $ldsLicense = LdsLicense::where('user_id', $this->user->id)->first();
      $this->assertTrue($ldsLicense !== null);
      $this->assertTrue($ldsLicense->license_free + $count === $ldsLicense->license_count);
    }

    public function regDeviceApi()
    {
      $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
      $response->assertStatus(200)
        ->assertJsonStructure($this->regResponseSchema);

      $ldsDevice = $this->findDevice($response->json('user_code'), $this->regRequest['device_id']);
      $this->assertTrue($ldsDevice !== null);

      return $response;
    }

    public function unregDeviceApi(string $user_code)
    {
      $unregRequest = $this->unregRequest;
      $unregRequest['user_code'] = $user_code;
      $response = $this->postJson($this->baseUrl . '/unreg-device', $unregRequest);
      $response->assertStatus(200);

      $ldsDevice = $this->findDevice($user_code, $unregRequest['device_id']);
      $this->assertTrue($ldsDevice == null);

      return $response;
    }

    public function findDevice(string $user_code, string $device_id): ?LdsDevice
    {
      return LdsLicense::fromUserId((new LdsCoding)->decodeUserId($user_code))->getDevice($device_id);
    }
  }
}
