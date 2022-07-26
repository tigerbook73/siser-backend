<?php

namespace Tests\Feature;

use App\Models\LdsRegistration;
use App\Services\Lds\LdsCoding;
use Tests\ApiTestCase;

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

  protected function getUserCode()
  {
    $response = $this->postJson('/api/v1/lds/reg-device', $this->regRequest);
    return $response->json('user_code');
  }

  protected function encodeRequest(array $request)
  {
    return (new LdsCoding)->encodeJsonText(json_encode($request));
  }
}
