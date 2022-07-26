<?php

namespace Tests\Feature;

use App\Models\User;

class AccountTestCase extends UserTestCase
{
  public string $baseUrl = '/api/v1/account';

  protected function setUp(): void
  {
    parent::setUp();

    $this->object = $this->user ?? User::first();
  }
}
