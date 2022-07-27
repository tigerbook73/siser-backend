<?php

namespace Tests\Feature;

use App\Models\Base\LdsInstance;
use App\Models\LdsPool;
use App\Models\User;
use App\Services\Lds\LdsCoding;

class LdsCheckOutApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsCheckOutOk()
  {
    $checkInRequest = $this->checkInRequest;
    $checkInRequest['user_code'] = $this->getUserCode();

    $paramString = http_build_query([
      'rq' => $this->encodeRequest($checkInRequest),
      'online' => 1,
    ]);

    // check-in
    $response = $this->get("/check-in?$paramString");
    $response->assertStatus(200);

    // check-out
    $response = $this->get("/check-out?$paramString");
    $response->assertStatus(200);

    /**
     * verify check-out results
     */
    $content = $response->getContent();
    $this->assertTrue(strpos($content, 'BeginLDSData') !== false);

    /** @var LdsInstance $ldsInstance */
    $ldsInstance = LdsInstance::where('user_code', $checkInRequest['user_code'])
      ->where('device_id', $checkInRequest['device_id'])
      ->first();
    $this->assertTrue($ldsInstance !== null && !$ldsInstance->online);

    /** @var LdsPool $ldsPool */
    $ldsPool = LdsPool::where('user_id', $this->user->id)->first();
    $this->assertTrue($ldsPool !== null);
    $this->assertTrue($ldsPool->license_free === $ldsPool->license_count);

    return $response;
  }
}
