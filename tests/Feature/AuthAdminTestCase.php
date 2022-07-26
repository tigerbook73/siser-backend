<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\ApiTestCase;

class AuthAdminTestCase extends AdminUserTestCase
{
  public string $baseUrl = '/api/v1/auth/admin';
}
