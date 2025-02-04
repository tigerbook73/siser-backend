<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingAccountListApiTest extends LicenseSharingTestCase
{
  public ?string $role = 'customer';

  protected function setUp(): void
  {
    parent::setUp();
    $owner = User::where('name', 'user1.test')->first();
    $this->actingAs($owner, 'api');
  }

  public function test_list_empty_ok()
  {
    $this->listAssert();
  }

  public function test_list_normal_ok()
  {
    $this->markTestIncomplete();

    // $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();
    // $this->listAssert(count: 1);

    // $this->manager->stopSubscription($licenseSharing->subscription, 'test');

    // $this->listAssert(count: 0);
  }
}
