<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Tests\ApiTestCase;

class MachineTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/machines';
  public string $model = Machine::class;


  public Machine $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      'id',
      'serial_no',
      'model',
      'nickname',
      'user_id',
    ];

    $this->modelCreate = [
      'serial_no' => "5555-6666-7777-9999",
      'model' => "TEST MODEL",
      'nickname' => "NONE",
      'user_id' => User::first()->id,
    ];

    $this->modelUpdate = [
      'model' => "TEST MODEL",
      'nickname' => "NONE",
    ];

    $this->object = Machine::first();
  }
}
