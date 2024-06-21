<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationAcceptTest extends LicenseSharingTestCase
{
  public function test_accept_license_sharing_invitation_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $this->service->acceptLicenseSharingInvitation($invitation);

    $this->assertEquals(LicenseSharingInvitation::STATUS_ACCEPTED, $invitation->getStatus());

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_accept_license_sharing_invitation_and_cancel_others_ok()
  {
    $licenseSharing1 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user1.test', count: 2);
    $licenseSharing2 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user2.test', count: 2);


    $guest = User::where('name', 'user3.test')->first();
    $invitation1 = $this->service->createLicenseSharingInvitation($licenseSharing1, $guest, '2099-12-31');
    $invitation2 = $this->service->createLicenseSharingInvitation($licenseSharing2, $guest, '2099-12-31');

    $this->service->acceptLicenseSharingInvitation($invitation1);

    $invitation1->refresh();
    $invitation2->refresh();
    $this->assertEquals(LicenseSharingInvitation::STATUS_ACCEPTED, $invitation1->getStatus());
    $this->assertEquals(LicenseSharingInvitation::STATUS_CANCELLED, $invitation2->getStatus());

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing1);
    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing2);
    $this->assertTrue(true);
  }



  public function test_accept_license_sharing_invitation_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    foreach ([
      LicenseSharingInvitation::STATUS_ACCEPTED,
      LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingInvitation::STATUS_REVOKED,
      LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingInvitation::STATUS_DELETED,
    ] as $status) {
      try {
        $invitation->status = $status;
        $invitation->save();

        $this->service->acceptLicenseSharingInvitation($invitation);
        $this->assertTrue(false, "Accept invitation with status $status shall fail!");
      } catch (\Exception $e) {
        $this->assertTrue(true);
      }
    }
  }
}
