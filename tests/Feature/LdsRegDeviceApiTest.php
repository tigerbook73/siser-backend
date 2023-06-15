<?php

namespace Tests\Feature;

class LdsRegDeviceApiTest extends LdsTestCase
{
  public ?string $role = 'customer';

  public function testLdsRegApiTest()
  {
    return $this->regDeviceApi();
  }

  public function testLdsRegApiDeviceIDEmptyFail()
  {
    $this->regRequest['device_id'] = '';
    $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_id', ['The device id field is required.']);
  }

  public function testLdsRegApiDeviceIDFail()
  {
    $this->regRequest['device_id'] = $this->createRandomString(15);
    $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_id', ['The device id must be 16 digits.']);
  }

  public function testLdsRegApiVersionFail()
  {
    $this->regRequest['version'] = '';
    $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.version', ['The version field is required.']);
  }

  public function testLdsRegApiDeviceNameEmptyFail()
  {
    $this->regRequest['device_name'] = '';
    $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_name', ['The device name field is required.']);
  }

  public function testLdsRegApiDeviceNameExceededLengthFail()
  {
    $this->regRequest['device_name'] = $this->createRandomString(256);
    $response = $this->postJson($this->baseUrl . '/reg-device', $this->regRequest);
    $response->assertStatus(422)
      ->assertJsonPath('errors.device_name', ['The device name must not be greater than 255 characters.']);
  }
}
