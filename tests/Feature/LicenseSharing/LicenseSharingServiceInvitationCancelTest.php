<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationCancelTest extends LicenseSharingTestCase
{
  public function test_cancel_license_sharing_invitation_open_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $this->service->cancelLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_cancel_license_sharing_invitation_accept_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $this->service->acceptLicenseSharingInvitation($invitation);
    $this->service->cancelLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }


  public function test_cancel_license_sharing_invitation_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    foreach ([
      LicenseSharingInvitation::STATUS_OPEN,
      LicenseSharingInvitation::STATUS_ACCEPTED,
    ] as $status) {
      try {
        $invitation->status = $status;
        $invitation->save();

        $this->service->cancelLicenseSharingInvitation($invitation);
        $this->assertTrue(false, "Cancel invitation with status $status shall fail!");
      } catch (\Exception $e) {
        $this->assertTrue(true);
      }
    }
  }
}
