<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationToMeTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationToMeAccountListApiTest extends LicenseSharingInvitationToMeTestCase
{
  public ?string $role = 'customer';
  public User $guest;

  protected function setUp(): void
  {
    parent::setUp();
    $this->guest = User::where('name', 'user2.test')->first();
    $this->actingAs($this->guest, 'api');
  }

  public function test_list_ok()
  {
    $licenseSharing1 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user1.test');
    $licenseSharing2 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user3.test');
    $licenseSharing3 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user4.test');

    $invitation1 = $this->service->createLicenseSharingInvitation($licenseSharing1, $this->guest, '2099-12-31');
    $this->listAssert(count: 1);

    $invitation2 = $this->service->createLicenseSharingInvitation($licenseSharing2, $this->guest, '2099-12-31');
    $invitation3 = $this->service->createLicenseSharingInvitation($licenseSharing3, $this->guest, '2099-12-31');
    $this->listAssert(count: 3);

    $this->service->cancelLicenseSharingInvitation($invitation2);
    $this->listAssert(count: 2);

    $this->service->acceptLicenseSharingInvitation($invitation3);
    $this->listAssert(count: 1);

    // TODO: cancelSubscription immediately
    // $this->manager->stopSubscription($licenseSharing3->subscription, 'test');

    // $this->listAssert(count: 0);
  }
}
