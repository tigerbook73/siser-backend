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
