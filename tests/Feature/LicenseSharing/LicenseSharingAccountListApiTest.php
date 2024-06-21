<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\Subscription;
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
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();
    $this->listAssert(count: 1);

    $licenseSharing->subscription->stop(Subscription::STATUS_STOPPED);
    $this->service->refreshLicenseSharing($licenseSharing);

    $this->listAssert(count: 0);
  }
}
