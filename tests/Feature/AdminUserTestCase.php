<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Tests\ApiTestCase;

class AdminUserTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/admin-users';
  public string $model = AdminUser::class;

  public AdminUser $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      "id",
      "name",
      "full_name",
      "email",
      "roles",
    ];

    $this->modelCreate = [
      "name"      =>  "admin2",
      "full_name" =>  "admin2",
      "email"     =>  "admin2@haha.com",
      "password"  =>  "password",
      "roles"     =>  ["admin"],
    ];

    $this->modelUpdate = [
      "full_name" => "On the fly",
      "password"  => "password",
      "roles"     => ["admin"],
    ];

    $this->object = AdminUser::first();
  }
}
