<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\Subscription;
use Tests\Feature\LicenseSharing\LicenseSharingTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingServiceLicenseSharingCreateTest extends LicenseSharingTestCase
{
  /**
   * test cases
   * - create license sharing
   *    + ok
   *    + nok (status, basic, package info(count), already active license sharing)
   * - update license sharing (no invitation)
   *    + status == stopped
   *    + package info == null
   *    + package info count == 0
   *    + package info count ++
   *    + package info count --
   * - create license sharing invitation
   *    + ok
   *    + nok (status, no free license, already invited, already active license sharing)
   */

  public function test_create_license_sharing_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing();

    LicenseSharingTestHelper::assertLicenseSharing($licenseSharing);
    $this->assertTrue(true);
  }

  public function test_create_license_sharing_inactive_nok()
  {
    $subscription = LicenseSharingTestHelper::createFakeSubscription();
    $subscription->setStatus(Subscription::STATUS_STOPPED);
    $subscription->save();

    $this->expectException(\Exception::class);
    $this->service->createLicenseSharing($subscription);
  }

  public function test_create_license_sharing_basic_nok()
  {
    $subscription = LicenseSharingTestHelper::createFakeSubscription();
    $subscription->subscription_level = 1;
    $subscription->save();

    $this->expectException(\Exception::class);
    $this->service->createLicenseSharing($subscription);
  }

  public function test_create_license_sharing_no_license_package_nok()
  {
    $subscription = LicenseSharingTestHelper::createFakeSubscription();
    $subscription->license_package_info = null;
    $subscription->save();

    $this->expectException(\Exception::class);
    $this->service->createLicenseSharing($subscription);
  }

  public function test_create_license_sharing_license_package_count_zero_nok()
  {
    $subscription = LicenseSharingTestHelper::createFakeSubscription();
    $subscription->license_package_info = ['quantity' => 0];
    $subscription->save();

    $this->expectException(\Exception::class);
    $this->service->createLicenseSharing($subscription);
  }

  public function test_create_license_sharing_already_active_license_sharing_nok()
  {
    $subscription = LicenseSharingTestHelper::createFakeSubscription();
    $this->service->createLicenseSharing($subscription);

    $this->expectException(\Exception::class);
    $this->service->createLicenseSharing($subscription);
  }
}
