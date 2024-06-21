<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationAccountListApiTest extends LicenseSharingInvitationTestCase
{
  public ?string $role = 'customer';
  public User $owner;

  protected function setUp(): void
  {
    parent::setUp();
    $this->owner = User::where('name', 'user1.test')->first();
    $this->actingAs($this->owner, 'api');
  }

  public function test_list_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user1.test');

    $guest1 = User::where('name', 'user2.test')->first();
    $guest2 = User::where('name', 'user3.test')->first();

    $this->listAssert(count: 0);

    $invitation1 = $this->service->createLicenseSharingInvitation($licenseSharing, $guest1, '2099-12-31');
    $this->listAssert(count: 1);

    $invitation2 = $this->service->createLicenseSharingInvitation($licenseSharing, $guest2, '2099-12-31');
    $this->listAssert(count: 2);

    $this->service->deleteLicenseSharingInvitation($invitation1);
    $this->listAssert(count: 1);

    $invitation1->refresh();
    $this->assertEquals(LicenseSharingInvitation::STATUS_DELETED, $invitation1->status);
  }
}
