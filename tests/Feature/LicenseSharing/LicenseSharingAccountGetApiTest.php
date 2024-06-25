<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\Subscription;
use App\Models\User;
use App\Services\DigitalRiver\SubscriptionManager;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingAccountGetApiTest extends LicenseSharingTestCase
{
  public ?string $role = 'customer';

  protected function setUp(): void
  {
    parent::setUp();
    $owner = User::where('name', 'user1.test')->first();
    $this->actingAs($owner, 'api');
  }

  public function test_get_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();
    $this->getAssert(id: $licenseSharing->id);
  }

  public function test_get_not_exist_nok()
  {
    $this->getAssert(status: 404, id: 9999);
  }

  public function test_get_void_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $this->manager->stopSubscription($licenseSharing->subscription, 'test');

    $this->getAssert(status: 404, id: $licenseSharing->id);
  }
}
