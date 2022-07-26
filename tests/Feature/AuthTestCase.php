<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;

class AuthTestCase extends UserTestCase
{
  public string $baseUrl = '/api/v1/auth';
}
