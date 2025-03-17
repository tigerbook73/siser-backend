<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\Subscription;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceLicenseSharingRefreshTest extends LicenseSharingTestCase
{
  public function test_refresh_license_sharing_no_invitation_status_stopped()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $licenseSharing->subscription->setStatus(Subscription::STATUS_STOPPED);
    $licenseSharing->subscription->save();

    $this->service->refreshLicenseSharing($licenseSharing);

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }

  public function test_refresh_license_sharing_no_invitation_package_info_null()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $licenseSharing->subscription->setLicensePackageInfo(null);
    $licenseSharing->subscription->save();

    $this->service->refreshLicenseSharing($licenseSharing);

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }

  public function test_refresh_license_sharing_no_invitation_package_info_count_increment()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $licensePackageInfo = $licenseSharing->subscription->getLicensePackageInfo();
    $licensePackageInfo->price_rate->quantity++;
    $licenseSharing->subscription->setLicensePackageInfo($licensePackageInfo);
    $licenseSharing->subscription->save();

    $this->service->refreshLicenseSharing($licenseSharing);

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }

  public function test_refresh_license_sharing_no_invitation_package_info_count_decrement()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    $licensePackageInfo = $licenseSharing->subscription->getLicensePackageInfo();
    $licensePackageInfo->price_rate->quantity--;
    $licenseSharing->subscription->setLicensePackageInfo($licensePackageInfo);
    $licenseSharing->subscription->save();

    $this->service->refreshLicenseSharing($licenseSharing);

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }
}
