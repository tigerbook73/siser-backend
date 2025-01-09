<?php

namespace Tests\Feature;

use App\Models\AdminUser;

class AdminUserDeleteApiTest extends AdminUserTestCase
{
  public ?string $role = 'admin';

  public function testAdminUserDeleteSuccess()
  {
    $adminUser = AdminUser::where('name', 'web-team')->first();
    $response = $this->deleteJson("$this->baseUrl/" . $adminUser->id);
    $response->assertStatus(200);
  }

  public function testAdminUserDeleteFailed()
  {
    $response = $this->deleteJson("$this->baseUrl/" . 999999);
    $response->assertStatus(404);
  }
}
