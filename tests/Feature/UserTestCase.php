<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;
use Tests\Models\User as ModelsUser;

class UserTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/users';
  public string $model = User::class;
  public User $object;

  public $machineSchema = [
    "id",
    "serial_no",
    "model",
    "nickname",
    "user_id",
  ];
  public $subscriptionSchema = [
    "id",
    "user_id",
    "plan_id",
    "plan_info" => [
      "id",
      "name",
      "product_name",
      "description",
      "subscription_level",
      "price" => [
        "country",
        "currency",
        "price",
      ],
    ],
    "currency",
    "price",
    "start_date",
    "end_date",
    "status",
  ];

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsUser);;

    // TODO: to be removed very soon
    unset($this->modelSchema[array_search('machine_count', $this->modelSchema)]);

    $this->modelCreate = [];
    $this->modelUpdate = [];
    $this->object = User::first();
  }
}
