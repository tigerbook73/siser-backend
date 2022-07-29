<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;

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
    "plan" => [
      "id",
      "name",
      "catagory",
      "description",
      "subscription_level",
      "contract_term",
      "price" => [
        "*" => [
          "price",
          "currency",
        ],
      ],
      "auto_renew",
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

    $this->modelSchema = [
      "id",
      "name",
      "given_name",
      "family_name",
      "full_name",
      "email",
      "phone_number",
      "cognito_id",
      "country_code",
      "language_code",
      "subscription_level",
      "license_count",
    ];

    $this->modelCreate = [];
    $this->modelUpdate = [];
    $this->object = User::first();
  }
}
