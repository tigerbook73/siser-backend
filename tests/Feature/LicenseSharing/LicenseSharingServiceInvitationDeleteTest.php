<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\User;
use Carbon\Carbon;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationDeleteTest extends LicenseSharingTestCase
{
  public function test_delete_license_sharing_invitation_open_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_delete_license_sharing_invitation_accepted_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->acceptLicenseSharingInvitation($invitation);
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_delete_license_sharing_invitation_cancelled_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->cancelLicenseSharingInvitation($invitation);
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_delete_license_sharing_invitation_revoked_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->revokeLicenseSharingInvitation($invitation);
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_delete_license_sharing_invitation_expired_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    Carbon::setTestNow(now()->addSeconds(2));
    $invitation = $this->service->expireLicenseSharingInvitation($invitation);
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing);
    $this->assertTrue(true);
  }

  public function test_delete_license_sharing_invitation_deleted_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(expiresAt: now()->addSecond());
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    $this->expectException(\Exception::class);
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);
  }
}
