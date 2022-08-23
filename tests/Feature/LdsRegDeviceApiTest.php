<?php

namespace Tests\Feature;

use App\Models\LdsInstance;

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

  public function testLdsRegApiDeviceIDEmptyFail()
  {
    $this->regRequest['device_id'] = '';
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_id', ['The device id field is required.']);
  }

  public function testLdsRegApiDeviceIDFail()
  {
    $this->regRequest['device_id'] = $this->createRandomString(15);
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_id', ['The device id must be 16 digits.']);
  }

  public function testLdsRegApiVersionFail()
  {
    $this->regRequest['version'] = '';
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.version', ['The version field is required.']);
  }

  public function testLdsRegApiDeviceNameEmptyFail()
  {
    $this->regRequest['device_name'] = '';
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_name', ['The device name field is required.']);
  }

  public function testLdsRegApiDeviceNameExceededLengthFail()
  {
    $this->regRequest['device_name'] = $this->createRandomString(256);
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_name', ['The device name must not be greater than 255 characters.']);
  }

  public function testLdsRegApiOnlineEmptyFail()
  {
    $this->regRequest['online'] = '';
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.online', ['The online field must have a value.']);
  }

  public function testLdsRegApiOnlineInvalidFail()
  {
    $this->regRequest['online'] = '2';
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.online', ['The selected online is invalid.']);
  }
}
