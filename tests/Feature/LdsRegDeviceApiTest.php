<?php

namespace Tests\Feature;

use App\Models\LdsInstance;
use App\Models\LdsPool;
use App\Models\LdsRegistration;
use App\Models\User;

class LdsRegDeviceApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsRegApiTest()
  {
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(200)
      ->assertJsonStructure($this->regResponseSchema);

    /** @var LdsInstance $ldsInstance */
    $ldsInstance = LdsInstance::where('user_code', $response->json('user_code'))
      ->where('device_id', $this->regRequest['device_id'])
      ->first();
    $this->assertTrue($ldsInstance !== null);

    return $response;
  }
}
