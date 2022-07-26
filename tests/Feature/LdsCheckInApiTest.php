<?php

namespace Tests\Feature;

use App\Models\Base\LdsInstance;
use App\Models\LdsPool;
use App\Models\User;
use App\Services\Lds\LdsCoding;

class LdsCheckInApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public $checkInRequest = [
    'version'     => 1,
    'request_id'  => '10101',
    'device_id'   => '0000111100002222',
    'user_code'   => '',
  ];

  public function testLdsCheckInOk()
  {
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();

    $paramString = http_build_query([
      'rq' => $this->encodeRequest($checkInRequest),
      'online' => 1,
    ]);

    $response = $this->get("/check-in?$paramString");
    $response->assertStatus(200);

    $content = $response->getContent();
    $this->assertTrue(strpos($content, 'BeginLDSData') !== false);

    /** @var LdsInstance $ldsInstance */
    $ldsInstance = LdsInstance::where('user_code', $checkInRequest['user_code'])
      ->where('device_id', $checkInRequest['device_id'])
      ->first();
    $this->assertTrue($ldsInstance !== null && $ldsInstance->online);

    /** @var LdsPool $ldsPool */
    $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
    $this->assertTrue($ldsPool !== null);
    $this->assertTrue($ldsPool->license_free + 1 === $ldsPool->license_count);

    return $response;
  }
}
