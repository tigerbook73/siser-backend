<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;

class UserTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/users';
  public string $model = User::class;


  public User $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      "id",
      "name",
      "full_name",
      "email",
      "cognito_id",
      "country",
      "language",
      "subscription_level",
    ];

    $this->modelCreate = [
      "create_from" => "username",
      "username" => "chin.lim",
    ];

    $this->modelUpdate = [];

    $this->object = User::first();
  }
}
