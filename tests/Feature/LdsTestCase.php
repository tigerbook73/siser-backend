<?php

namespace App\Services\Lds {
  function time()
  {
    return \Tests\Feature\LdsTestCase::fakeTime();
  }
}

namespace Tests\Feature {

  use App\Models\LdsRegistration;
  use App\Services\Lds\LdsCoding;
  use Tests\ApiTestCase;
  use App\Models\Base\LdsInstance;
  use App\Models\LdsPool;
  use App\Services\Lds\LdsException;
  use Tests\Helper\ApiTestTimeHelper;

  class LdsTestCase extends ApiTestCase
  {
    public string $baseUrl = '/api/v1/lds';
    public string $model = LdsRegistration::class;

    public $regRequest = [
      'version' => 1,
      'device_id' => '0000111100002222',
      'device_name' => 'test-computer',
      'online' => 1,
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
      /** @var LdsInstance $ldsInstance */
      $ldsInstance = LdsInstance::where('user_code', $checkInRequest['user_code'])
        ->where('device_id', $checkInRequest['device_id'])
        ->first();
      if ($isOnline) {
        $this->assertTrue($ldsInstance !== null && $ldsInstance->online);
      } else {
        $this->assertTrue($ldsInstance !== null && !$ldsInstance->online);
      }

      /** @var LdsPool $ldsPool */
      $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
      $this->assertTrue($ldsPool !== null);
      $this->assertTrue($ldsPool->license_free + $count === $ldsPool->license_count);
    }

    public function regDeviceApi()
    {
      $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
      $response->assertStatus(200)
        ->assertJsonStructure($this->regResponseSchema);

      /** @var LdsInstance $ldsInstance */
      $ldsInstance = LdsInstance::where('user_code', $response->json('user_code'))
        ->where('device_id', $this->regRequest['device_id'])
        ->first();
      $this->assertTrue($ldsInstance !== null);

      return $response;
    }

    public function findInstance(string $user_code, string $device_id): ?LdsInstance
    {
      return LdsInstance::where('user_code', $user_code)->where('device_id', $device_id)->first();
    }
  }
}
