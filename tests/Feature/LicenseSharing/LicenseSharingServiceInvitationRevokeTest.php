<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationRevokeTest extends LicenseSharingTestCase
{
  public function test_revoke_license_sharing_invitation_open_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $this->service->revokeLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_revoke_license_sharing_invitation_accepted_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $invitation = $this->service->acceptLicenseSharingInvitation($invitation);
    $this->service->revokeLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }


  public function test_revoke_license_sharing_invitation_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    foreach ([
      LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingInvitation::STATUS_REVOKED,
      LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingInvitation::STATUS_DELETED,
    ] as $status) {
      try {
        $invitation->status = $status;
        $invitation->save();

        $this->service->cancelLicenseSharingInvitation($invitation);
        $this->assertTrue(false, "Revoke invitation with status $status shall fail!");
      } catch (\Exception $e) {
        $this->assertTrue(true);
      }
    }
  }
}
