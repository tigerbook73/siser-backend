<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationExpireTest extends LicenseSharingTestCase
{
  public function test_expire_license_sharing_invitation_open_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    Carbon::setTestNow(now()->addSeconds(2));

    $this->service->expireLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_expire_license_sharing_invitation_accepted_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->AcceptLicenseSharingInvitation($invitation);
    Carbon::setTestNow(now()->addSeconds(2));

    $this->service->expireLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_expire_license_sharing_invitation_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());

    foreach ([
      LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingInvitation::STATUS_REVOKED,
      LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingInvitation::STATUS_DELETED,
    ] as $status) {
      try {
        $invitation->status = $status;
        $invitation->save();

        $this->service->expireLicenseSharingInvitation($invitation);
        $this->assertTrue(false, "Expire invitation with status $status shall fail!");
      } catch (\Exception $e) {
        $this->assertTrue(true);
      }
    }
  }
}
