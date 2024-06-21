<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\Subscription;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceInvitationCreateTest extends LicenseSharingTestCase
{
  public function test_create_license_sharing_invitation_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $guest = User::where('name', 'user2.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');

    $guest = User::where('name', 'user3.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }

  public function test_create_license_sharing_invitation_guest_pro_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $guest = User::where('name', 'user2.test')->first();
    $guest->subscription_level = 2;
    $guest->save();

    $this->expectException(\Exception::class);
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');
  }

  public function test_create_license_sharing_invitation_status_void_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $licenseSharing->subscription->setStatus(Subscription::STATUS_STOPPED);
    $licenseSharing->subscription->save();
    $this->service->refreshLicenseSharing($licenseSharing);

    $this->expectException(\Exception::class);

    $guest = User::where('name', 'user2.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');
  }

  public function test_create_license_sharing_invitation_no_free_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $guest = User::where('name', 'user2.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');

    $guest = User::where('name', 'user3.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');

    $this->expectException(\Exception::class);

    $guest = User::where('name', 'user4.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');
  }

  public function test_create_license_sharing_invitation_invite_owner_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $this->expectException(\Exception::class);

    $guest = User::where('name', 'user1.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');
  }

  public function test_create_license_sharing_invitation_re_invite_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $guest = User::where('name', 'user2.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');

    $this->expectException(\Exception::class);
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, '2099-12-31');
  }

  public function test_create_license_sharing_invitation_expired_nok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $this->expectException(\Exception::class);

    $guest = User::where('name', 'user2.test')->first();
    $invitation = $this->service->createLicenseSharingInvitation($licenseSharing, $guest, now()->subSecond());
  }
}
